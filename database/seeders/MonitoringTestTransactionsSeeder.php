<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Carbon;

class MonitoringTestTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('transactions')->truncate();

       $baseMinute = now()->subMinutes(2)->startOfMinute();

        // 10:00 bucket
        $this->insertVendorScenario($baseMinute->copy(), 'MTN', 500, 0, 200);
        $this->insertVendorScenario($baseMinute->copy(), 'Airtel', 400, 100, 300);

        // 10:01 bucket
        $this->insertVendorScenario($baseMinute->copy()->addMinute(), 'MTN', 600, 400, 1200);
        $this->insertVendorScenario($baseMinute->copy()->addMinute(), 'Airtel', 900, 100, 250);

        $this->command->info('Mixed vendor deterministic test data inserted.');
    }

    private function insertVendorScenario($minute, $vendor, $successCount, $failedCount, $latency)
    {
        $rows = [];

        for ($i = 0; $i < $successCount; $i++) {
            $rows[] = $this->makeRow($minute, $vendor, 'SUCCESS', $latency, 'S_' . uniqid());
        }

        for ($i = 0; $i < $failedCount; $i++) {
            $rows[] = $this->makeRow($minute, $vendor, 'FAILED', $latency, 'F_' . uniqid());
        }

        DB::table('transactions')->insert($rows);
    }

    private function makeRow($minute, $vendor, $status, $latency, $reference)
    {
        return [
            'source'        => 'bank_db',
            'client_code'   => 'ZENITH',
            'service_type'  => 'SMS',
            'vendor_code'   => $vendor,
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
