<?php

namespace App\Console\Commands;

use App\Models\Incident;
use App\Models\Transaction;
use Illuminate\Console\Command;

class DetectSmsIncidents extends Command
{
    protected $signature = 'incidents:detect-sms';
    protected $description = 'Detect SMS incidents based on failure rate';

    public function handle(): int
    {
        $from = now()->subMinutes(10);

        $stats = Transaction::selectRaw("
                COUNT(*) as total,
                SUM(status = 'FAILED') as failed,
                ROUND(
                    (SUM(status = 'FAILED') / NULLIF(COUNT(*), 0)) * 100,
                    2
                ) as failure_rate
            ")
            ->where('occurred_at', '>=', $from)
            ->where('service_type', 'LIKE', 'SMS%')
            ->first();

        // Nothing to evaluate
        if (! $stats || $stats->total == 0) {
            return self::SUCCESS;
        }

        // Threshold check
        if ($stats->failure_rate <= 5) {
            return self::SUCCESS;
        }

        // Prevent duplicates
        $exists = Incident::where('service_type', 'SMS')
            ->where('scope_type', 'global')
            ->where('status', 'open')
            ->exists();

        if ($exists) {
            return self::SUCCESS;
        }

        // Create incident
        Incident::create([
            'service_type' => 'SMS',
            'scope_type'   => 'global',
            'scope_code'   => null,
            'status'       => 'open',
            'failure_rate' => $stats->failure_rate,
            'started_at'   => now(),
        ]);


        // Auto-resolution check
            $openIncident = Incident::where('service_type', 'SMS')
                ->where('scope_type', 'global')
                ->where('status', 'open')
                ->first();

            if ($openIncident && $stats->failure_rate < 2) {
                $openIncident->update([
                    'status'      => 'resolved',
                    'resolved_at' => now(),
                ]);
            }


        return self::SUCCESS;
    }
}

