<?php

namespace App\Services\Observability\Normalizers;

use App\Models\DataSource;
use App\Services\Observability\Normalizers\Contracts\NormalizerInterface;

class DiscoNormalizer implements NormalizerInterface
{
    protected array $billerMap;

    public function __construct($billerMap)
    {
        $this->billerMap = $billerMap->toArray();
    }

    public function normalize($request, DataSource $source): array
    {
        $status = $this->normalizeStatus($request->status);

        $billerName = $this->billerMap[$request->biller_id] ?? null;

        return [
            'data_source_id' => $source->id,
            'source'         => 'disco_db',
            'client_code'    => null,
            'service_type'   => 'ELECTRICITY',
            'vendor_code'    => $billerName,
            'reference'      => $request->trans_code,
            'amount'         => $request->amount,
            'status'         => $status,
            'raw_status'     => $request->response,
            'error_code'     => $status === 'FAILED' ? $request->response : null,
            'latency_ms'     => null, // only if measurable
            'raw_request'    => [
                'meterNo'   => $request->meterNo,
                'type'      => $request->type,
                'biller_id' => $request->biller_id,
            ],
            'raw_response'   => [
                'response'  => $request->response,
            ],
            'occurred_at'    => $request->created_at,
        ];
    }

    protected function normalizeStatus(string $status): string
    {
        return match ($status) {
            '1' => 'SUCCESS',
            '2' => 'FAILED',
            default => 'PENDING',
        };
    }
}
