<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class MonitorDashboardController extends Controller
{
    public function __invoke()
    {
        // 🔹 Live health window
        $from = now()->subHour();

        $base = Transaction::where('occurred_at', '>=', $from);

        // =============================
        // Global Stats
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

        $stats['avg_latency'] = (clone $base)
            ->whereNotNull('latency_ms')
            ->avg('latency_ms');

        $stats['avg_latency'] = $stats['avg_latency']
            ? round($stats['avg_latency'])
            : null;

        // =============================
        // Breakdown by Service
        // =============================

        $byService = (clone $base)
            ->selectRaw('
                service_type,
                COUNT(*) as total,
                SUM(status = "FAILED") as failed,
                SUM(status = "PENDING") as pending
            ')
            ->groupBy('service_type')
            ->get()
            ->map(function ($row) {
                $row->failure_rate = $row->total
                    ? round(($row->failed / $row->total) * 100, 2)
                    : 0;
                return $row;
            });

        // =============================
        // Breakdown by Vendor
        // =============================

        $byVendor = (clone $base)
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
        // Breakdown by Client
        // =============================

                $byClient = (clone $base)
        ->selectRaw('
            client_code,
            COUNT(*) as total,
            SUM(status = "FAILED") as failed,
            SUM(status = "PENDING") as pending
        ')
        ->whereNotNull('client_code')
        ->groupBy('client_code')
        ->get()
        ->map(function ($row) {
            $row->failure_rate = $row->total
                ? round(($row->failed / $row->total) * 100, 2)
                : 0;
            return $row;
        });


        // =============================
        // Raw Status Distribution
        // =============================

        $byRawStatus = (clone $base)
            ->selectRaw('raw_status, COUNT(*) as total')
            ->groupBy('raw_status')
            ->orderByDesc('total')
            ->get();

        return view('dashboards.monitor', [
            'stats'       => (object) $stats,
            'byService'   => $byService,
            'byVendor'    => $byVendor,
            'byRawStatus' => $byRawStatus,
            'byClient' => $byClient,
        ]);
    }
}
