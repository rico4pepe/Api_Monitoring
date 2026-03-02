<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AggregateMonitoringRollups extends Command
{
    // Example:
    // php artisan rollups:aggregate
    // php artisan rollups:aggregate --from="2026-02-20 00:00:00" --to="2026-02-20 23:59:59"

    protected $signature = 'rollups:aggregate 
                            {--from=} 
                            {--to=}';

    protected $description = 'Aggregate transactions into minute-based monitoring rollups';

    public function handle()
    {
        $now = now()->startOfMinute();
        $lastClosedMinute = $now->copy()->subMinute();

        $fromOption = $this->option('from');
        $toOption   = $this->option('to');

        if ($fromOption && $toOption) {

            // Backfill mode
            $from = Carbon::parse($fromOption)->startOfMinute();
            $to   = Carbon::parse($toOption)->endOfMinute();

        } else {

            // Incremental mode
            $lastAggregated = DB::table('monitoring_rollups')
                ->max('time_bucket');

            if ($lastAggregated) {
                $from = Carbon::parse($lastAggregated)->addMinute();
            } else {
                $firstOccurred = DB::table('transactions')
                    ->min(DB::raw('DATE_FORMAT(occurred_at, "%Y-%m-%d %H:%i:00")'));

                $from = $firstOccurred
                    ? Carbon::parse($firstOccurred)
                    : null;
            }

            $to = $lastClosedMinute;
        }

        if (!$from || $from > $to) {
            $this->info('Nothing to aggregate.');
            return Command::SUCCESS;
        }

        $this->info("Aggregating from {$from} to {$to}");

        $aggregates = DB::table('transactions')
            ->selectRaw('
                DATE_FORMAT(occurred_at, "%Y-%m-%d %H:%i:00") as time_bucket,
                service_type,
                vendor_code,
                client_code,
                COUNT(*) as total,
                SUM(status = "SUCCESS") as success_count,
                SUM(status = "FAILED") as failed_count,
                SUM(status = "PENDING") as pending_count,
                AVG(latency_ms) as avg_latency
            ')
            ->whereBetween('occurred_at', [$from, $to])
            ->groupBy(
                'time_bucket',
                'service_type',
                'vendor_code',
                'client_code'
            )
            ->get();

        foreach ($aggregates as $row) {

            $failureRate = $row->total > 0
                ? round(($row->failed_count / $row->total) * 100, 2)
                : 0;

            DB::table('monitoring_rollups')->updateOrInsert(
                [
                    'time_bucket'  => $row->time_bucket,
                    'service_type' => $row->service_type,
                    'vendor_code'  => $row->vendor_code,
                    'client_code'  => $row->client_code,
                ],
                [
                    'total'         => $row->total,
                    'success_count' => $row->success_count,
                    'failed_count'  => $row->failed_count,
                    'pending_count' => $row->pending_count,
                    'avg_latency'   => round($row->avg_latency ?? 0, 2),
                    'failure_rate'  => $failureRate,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]
            );
        }

        $this->info("Processed {$aggregates->count()} rollup rows.");

        return Command::SUCCESS;
    }
}
