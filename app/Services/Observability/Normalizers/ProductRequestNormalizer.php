<?php

namespace App\Services\Observability\Normalizers;

use App\Models\DataSource;
use App\Services\Observability\Normalizers\Contracts\NormalizerInterface;

class ProductRequestNormalizer implements NormalizerInterface
{
    protected array $billerMap;
    protected array $categoryTypeMap;
    protected array $categoryNameMap;
    protected array $productMap;

    public function __construct($billerMap, $categoryMapType, $categoryMapName, $productMap)
    {
        $this->billerMap = $billerMap->toArray();
        $this->categoryTypeMap = $categoryMapType->toArray();
        $this->categoryNameMap = $categoryMapName->toArray();
        $this->productMap = $productMap->toArray();
    }

    public function normalize($request, DataSource $source): array
    {
        $status = $this->normalizeStatus($request->status);
        $productName = $this->resolveProductName($request);

        return [
            'data_source_id' => $source->id,
            'source'         => 'product_db',
            'service_type'   => 'PRODUCT',
            'vendor_code'    => $this->billerMap[$request->biller_id] ?? null,
            'product_name'   => $productName,
            'reference'      => $request->trans_code,
            'amount'         => $request->amount,
            'status'         => $status,
            'raw_status'     => $request->response,
            'error_code'     => $status === 'FAILED' ? $request->response : null,
            'latency_ms'     => null,
            'raw_response'   => [
                'response' => $request->response ?? null,
                'status' => $request->status ?? null,
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

    protected function resolveProductName($request): ?string
    {
        if (! empty($request->product_id) && isset($this->productMap[$request->product_id])) {
            return $this->productMap[$request->product_id];
        }

        if (! empty($request->category_id) && isset($this->categoryNameMap[$request->category_id])) {
            return $this->categoryNameMap[$request->category_id];
        }

        return null;
    }
}
