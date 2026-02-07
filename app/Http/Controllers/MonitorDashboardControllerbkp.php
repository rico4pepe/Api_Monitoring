<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class MonitorDashboardController extends Controller
{
    
     public function __invoke()
    {
        $from = now()->subMinutes(15);

        $stats = Transaction::selectRaw("
                COUNT(*) as total,
                SUM(status = 'SUCCESS') as success,
                SUM(status = 'FAILED') as failed,
                SUM(status = 'PENDING') as pending,
                ROUND((SUM(status = 'FAILED') / COUNT(*)) * 100, 2) as failure_rate,
                ROUND(AVG(latency_ms)) as avg_latency
            ")
            ->where('occurred_at', '>=', $from)
            ->first();

        $breakdown = Transaction::selectRaw("
                service_type,
                vendor_code,
                source,
                COUNT(*) as total,
                SUM(status = 'FAILED') as failed,
                ROUND((SUM(status = 'FAILED') / COUNT(*)) * 100, 2) as failure_rate
            ")
            ->where('occurred_at', '>=', $from)
            ->groupBy('service_type', 'vendor_code', 'source')
            ->orderByDesc('failure_rate')
            ->get();

              $smsPerBank = Transaction::selectRaw("
            client_code,
            COUNT(*) as total,
            SUM(status = 'FAILED') as failed,
            ROUND(
                (SUM(status = 'FAILED') / NULLIF(COUNT(*), 0)) * 100,
                2
            ) as failure_rate
        ")
        ->where('occurred_at', '>=', $from)
        ->where('service_type', 'LIKE', 'SMS%')
        ->whereNotNull('client_code')
        ->groupBy('client_code')
        ->orderByDesc('failure_rate')
        ->get();

        $smsPerTelco = Transaction::selectRaw("
        vendor_code,
        COUNT(*) as total,
        SUM(status = 'FAILED') as failed,
        ROUND(
            (SUM(status = 'FAILED') / NULLIF(COUNT(*), 0)) * 100,
            2
        ) as failure_rate
    ")
    ->where('occurred_at', '>=', $from)
    ->where('service_type', 'LIKE', 'SMS%')
    ->whereNotNull('vendor_code')
    ->groupBy('vendor_code')
    ->orderByDesc('failure_rate')
    ->get();

    $smsTrend = Transaction::selectRaw("
        DATE_FORMAT(occurred_at, '%Y-%m-%d %H:%i') as time_bucket,
        COUNT(*) as total,
        SUM(status = 'FAILED') as failed
    ")
    ->where('occurred_at', '>=', $from)
    ->where('service_type', 'LIKE', 'SMS%')
    ->groupBy('time_bucket')
    ->orderBy('time_bucket')
    ->get();


          return view('dashboards.monitor', compact(
                'stats',
                'breakdown',
                'smsPerBank',
                'smsPerTelco',
                'smsTrend'
            ));
    }
}

