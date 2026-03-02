<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class ClientDetailController extends Controller
{
    public function __invoke($client)
    {
        $from = now()->subHour();

        $base = Transaction::where('client_code', $client)
            ->where('occurred_at', '>=', $from);

        // =============================
        // Summary
        // =============================

        $stats = [
            'total'   => (clone $base)->count(),
            'success' => (clone $base)->where('status', 'SUCCESS')->count(),
            'failed'  => (clone $base)->where('status', 'FAILED')->count(),
            'pending' => (clone $base)->where('status', 'PENDING')->count(),
        ];

        $stats['failure_rate'] = $stats['total']
            ? round(($stats['failed'] / $stats['total']) * 100, 2)
            : 0;

        // =============================
        // Breakdown by Telco
        // =============================

        $byTelco = (clone $base)
            ->selectRaw('
                vendor_code,
                COUNT(*) as total,
                SUM(status = "FAILED") as failed,
                SUM(status = "PENDING") as pending
            ')
            ->whereNotNull('vendor_code')
            ->groupBy('vendor_code')
            ->get()
            ->map(function ($row) {
                $row->failure_rate = $row->total
                    ? round(($row->failed / $row->total) * 100, 2)
                    : 0;
                return $row;
            });

        // =============================
        // Raw Status Breakdown
        // =============================

        $byRawStatus = (clone $base)
            ->selectRaw('raw_status, COUNT(*) as total')
            ->groupBy('raw_status')
            ->orderByDesc('total')
            ->get();

        // =============================
        // Recent Failed Transactions
        // =============================

        $recentFailures = (clone $base)
            ->where('status', 'FAILED')
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get([
                'phone',
                'vendor_code',
                'raw_status',
                'occurred_at'
            ]);

        return view('dashboards.client-detail', [
            'client'         => $client,
            'stats'          => (object) $stats,
            'byTelco'        => $byTelco,
            'byRawStatus'    => $byRawStatus,
            'recentFailures' => $recentFailures,
        ]);
    }
}
