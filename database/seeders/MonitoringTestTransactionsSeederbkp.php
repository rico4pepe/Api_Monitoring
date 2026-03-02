<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class MonitoringTestTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('transactions')->truncate();

        $baseDate = Carbon::create(2026, 2, 1, 10, 0, 0);

        // Scenario A — Success Heavy (10:00)
        $this->insertScenario(
            $baseDate->copy(),
            980,
            20,
            200
        );

        // Scenario B — Failure Spike (10:01)
        $this->insertScenario(
            $baseDate->copy()->addMinute(),
            600,
            400,
            1200
        );

        $this->command->info('Deterministic monitoring test data inserted.');
    }

    private function insertScenario($minute, $successCount, $failedCount, $latency)
    {
        $rows = [];

        for ($i = 0; $i < $successCount; $i++) {
            $rows[] = $this->makeRow($minute, 'SUCCESS', $latency, 'success_' . uniqid());
        }

        for ($i = 0; $i < $failedCount; $i++) {
            $rows[] = $this->makeRow($minute, 'FAILED', $latency, 'failed_' . uniqid());
        }

        DB::table('transactions')->insert($rows);
    }

    private function makeRow($minute, $status, $latency, $reference)
    {
        return [
            'source'        => 'bank_db',
            'client_code'   => 'ZENITH',
            'service_type'  => 'SMS',
            'vendor_code'   => 'Airtel',
            'product_name'  => null,
            'endpoint'      => null,
            'reference'     => $reference,
            'phone'         => '+2348000000000',
            'status'        => $status,
            'raw_status'    => null,
            'error_code'    => null,
            'latency_ms'    => $latency,
            'raw_request'   => null,
            'raw_response'  => null,
            'occurred_at'   => $minute->copy()->addSeconds(rand(0, 59)),
            'created_at'    => now(),
            'updated_at'    => now(),
            'amount'        => null,
            'data_source_id'=> null,
        ];
    }
}
