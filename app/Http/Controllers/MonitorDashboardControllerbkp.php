<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\DeliveryState\DeliveryStateResolver;
use Carbon\CarbonImmutable;

class MonitorDashboardController extends Controller
{
    public function __invoke(DeliveryStateResolver $resolver)
    {
        //$from = now()->subMinutes(15);
        $from =   now()->subDays(7);
      
        $now  = CarbonImmutable::now();

        $transactions = Transaction::where('occurred_at', '>=', $from)->get();
       

        $stats = [
            'total'            => 0,
            'delivered'        => 0,
            'terminal_failure' => 0,
            'pending'          => 0,
            'aged_pending'     => 0,
            'avg_latency'      => null,
        ];

        $latencies = [];

        $perBank  = [];
        $perTelco = [];

        foreach ($transactions as $tx) {
            $result = $resolver->resolve([
                'status'        => $tx->status,
                'occurred_at'   => $tx->occurred_at,
                'updated_at'    => $tx->updated_at,
                'dlr_status'    => $tx->dlr_status ?? null,
                'raw_response'  => $tx->raw_response,
                'error_code'    => $tx->error_code,
            ], $now);

            $stats['total']++;

            if ($tx->latency_ms !== null) {
                $latencies[] = $tx->latency_ms;
            }

            if ($result->deliveryState === 'delivered') {
                $stats['delivered']++;
            }

            if ($result->failureClass !== null) {
                $stats['terminal_failure']++;
            }

            if ($result->deliveryState === 'pending') {
                $stats['pending']++;

                if ($result->isAgedPending) {
                    $stats['aged_pending']++;
                }
            }

            /* =========================
             | Bank breakdown (SMS only)
             |========================= */

            if ($tx->service_type && str_starts_with($tx->service_type, 'SMS') && $tx->client_code) {
                $perBank[$tx->client_code]['total'] ??= 0;
                $perBank[$tx->client_code]['failure'] ??= 0;

                $perBank[$tx->client_code]['total']++;

                if ($result->failureClass !== null) {
                    $perBank[$tx->client_code]['failure']++;
                }
            }

            /* =========================
             | Telco breakdown (SMS only)
             |========================= */

            if ($tx->service_type && str_starts_with($tx->service_type, 'SMS') && $tx->vendor_code) {
                $perTelco[$tx->vendor_code]['total'] ??= 0;
                $perTelco[$tx->vendor_code]['failure'] ??= 0;

                $perTelco[$tx->vendor_code]['total']++;

                if ($result->failureClass !== null) {
                    $perTelco[$tx->vendor_code]['failure']++;
                }
            }
        }

        if (count($latencies)) {
            $stats['avg_latency'] = round(array_sum($latencies) / count($latencies));
        }

        // Final computed rates
        $stats['failure_rate'] = $stats['total']
            ? round(($stats['terminal_failure'] / $stats['total']) * 100, 2)
            : 0;

        /* =========================
         | Normalize breakdowns
         |========================= */

        $smsPerBank = collect($perBank)->map(function ($row, $bank) {
            return (object) [
                'client_code'  => $bank,
                'total'        => $row['total'],
                'failed'       => $row['failure'],
                'failure_rate' => $row['total']
                    ? round(($row['failure'] / $row['total']) * 100, 2)
                    : 0,
            ];
        })->sortByDesc('failure_rate')->values();

        $smsPerTelco = collect($perTelco)->map(function ($row, $telco) {
            return (object) [
                'vendor_code'  => $telco,
                'total'        => $row['total'],
                'failed'       => $row['failure'],
                'failure_rate' => $row['total']
                    ? round(($row['failure'] / $row['total']) * 100, 2)
                    : 0,
            ];
        })->sortByDesc('failure_rate')->values();

        // $stats = (object) $stats;
        return view('dashboards.monitor', compact(
            'stats',
            'smsPerBank',
            'smsPerTelco'
        ));
    }
}
