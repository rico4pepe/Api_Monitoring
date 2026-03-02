<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class MonitorDashboardController extends Controller
{
   public function __invoke()
{
    $from = now()->subDays(7);

    $base = Transaction::where('occurred_at', '>=', $from);

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

    // Breakdown by Service
    $byService = (clone $base)
        ->selectRaw('service_type, COUNT(*) as total,
            SUM(status = "FAILED") as failed')
        ->groupBy('service_type')
        ->get();

    // Breakdown by Vendor
    $byVendor = (clone $base)
        ->selectRaw('vendor_code, COUNT(*) as total,
            SUM(status = "FAILED") as failed')
        ->whereNotNull('vendor_code')
        ->groupBy('vendor_code')
        ->get();
    //Breakdown by Product
   $byProduct = (clone $base)
    ->selectRaw('
        vendor_code,
        product_name,
        COUNT(*) as total,
        SUM(status = "FAILED") as failed,
        SUM(status = "PENDING") as pending
    ')
    ->whereNotNull('product_name')
    ->groupBy('vendor_code', 'product_name')
    ->get()
    ->map(function ($row) {
        $row->failure_rate = $row->total
            ? round(($row->failed / $row->total) * 100, 2)
            : 0;
        return $row;
    });

    // Granular state breakdown
    $byRawStatus = (clone $base)
        ->selectRaw('raw_status, COUNT(*) as total')
        ->groupBy('raw_status')
        ->orderByDesc('total')
        ->get();

    return view('dashboards.monitor', [
        'stats'        => (object) $stats,
        'byService'    => $byService,
        'byVendor'     => $byVendor,
        'byRawStatus'  => $byRawStatus,
        'byProduct'    => $byProduct,
    ]);
}

}
