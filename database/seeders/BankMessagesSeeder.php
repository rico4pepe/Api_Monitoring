<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BankMessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('bank_messages')->truncate();

        $rows = [
            [
                'id' => 1,
                'user_id' => 1,
                'msisdn' => '+2348031234567',
                'pages' => 1,
                'text' => 'Hello from Alpha Your test message.',
                'response' => 'Accepted',
                'dlr_status' => 'DELIVRD',
                'dlr_report' => 1,
                'dlr' => '1',
                'status' => '1',
                'senderid' => 'ZenithBank',
                'counter' => '0',
                'dlr_request' => '',
                'dlr_results' => null,
                'network' => 'MTN',
                'user_dlr_url' => 'http://localhost:5173/dlr/callback',
                'created_at' => Carbon::parse('2025-11-16 09:30:00'),
                'updated_at' => Carbon::parse('2025-11-16 09:32:15'),
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'msisdn' => '+2348029876543',
                'pages' => 1,
                'text' => 'Hello from Alpha Your test message.',
                'response' => 'Accepted',
                'dlr_status' => 'UNDELIV',
                'dlr_report' => 1,
                'dlr' => '1',
                'status' => '1',
                'senderid' => 'ZenithBank',
                'counter' => '0',
                'dlr_request' => '',
                'dlr_results' => json_encode([
                    'error' => 'err:254',
                    'description' => 'Unknown subscriber'
                ]),
                'network' => 'Airtel',
                'user_dlr_url' => 'http://localhost:5173/dlr/callback',
                'created_at' => Carbon::parse('2025-11-16 13:45:00'),
                'updated_at' => Carbon::parse('2025-11-16 13:46:00'),
            ],
            // 👉 you can continue copying rows exactly like your sample
        ];

        DB::table('bank_messages')->insert($rows);
    }


}

