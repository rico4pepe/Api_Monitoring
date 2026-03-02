<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class VendorController extends Controller
{
    public function __invoke($vendor)
    {
        $from = now()->subHour();

        $base = Transaction::where('vendor_code', $vendor)
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
        // Breakdown by Product
        // =============================

        $byProduct = (clone $base)
            ->selectRaw('
                product_name,
                COUNT(*) as total,
                SUM(status = "FAILED") as failed,
                SUM(status = "PENDING") as pending
            ')
            ->whereNotNull('product_name')
            ->groupBy('product_name')
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
                'product_name',
                'phone',
                'raw_status',
                'occurred_at'
            ]);

        return view('dashboards.vendor-detail', [
            'vendor'         => $vendor,
            'stats'          => (object) $stats,
            'byProduct'      => $byProduct,
            'byRawStatus'    => $byRawStatus,
            'recentFailures' => $recentFailures,
        ]);
    }
}
