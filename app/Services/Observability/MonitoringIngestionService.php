<?php

namespace App\Services\Observability;

use App\Models\DataSource;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Observability\Normalizers\SmsNormalizer;
use App\Services\Observability\Normalizers\DiscoNormalizer;
use App\Services\Observability\Normalizers\ProductRequestNormalizer;

class MonitoringIngestionService
{
    protected int $windowMinutes = 15;

    public function ingest(DataSource $source): void
    {
        $config = $source->config;
        $connection = $config['connection'];

        Log::info('MONITORING INGEST START', [
            'source_code' => $source->code,
            'normalizer'  => $source->normalizer,
        ]);

        $rows = $this->fetchRows($source, $config);

        if ($rows->isEmpty()) {
            $source->last_polled_at = now();
            $source->save();
            return;
        }

        DB::transaction(function () use ($rows, $source, $config, $connection) {

            $normalizer = $this->resolveNormalizer($source, $connection);

            foreach ($rows as $row) {

                $data = $normalizer->normalize($row, $source);

                Transaction::updateOrCreate(
                    ['reference' => $data['reference']],
                    $data
                );
            }

            // Advance cursor based only on highest ID seen
            $source->cursor = $rows->last()->{$config['id_column']};
            $source->last_polled_at = now();
            $source->save();
        });

        Log::info('MONITORING INGEST COMPLETE', [
            'source_code' => $source->code,
            'count'       => $rows->count(),
        ]);
    }

    protected function fetchRows(DataSource $source, array $config)
    {
        $cursor = $source->cursor ?? 0;
        $windowStart = now()->subMinutes($this->windowMinutes);

        return DB::connection($config['connection'])
            ->table($config['table'])
            ->where(function ($query) use ($config, $cursor, $windowStart) {
                $query->where($config['id_column'], '>', $cursor)
                      ->orWhere('created_at', '>=', $windowStart);
            })
            ->orderBy($config['id_column'])
            ->limit(1000)
            ->get();
    }

    protected function resolveNormalizer(DataSource $source, string $connection)
    {
        return match ($source->normalizer) {

            'sms' => new SmsNormalizer(),

            'product_request' => $this->buildProductRequestNormalizer($connection),
            'disco' => $this->buildDiscoNormalizer($connection),

            default => throw new \RuntimeException(
                "Unsupported normalizer: {$source->normalizer}"
            ),
        };
    }

    protected function buildProductRequestNormalizer(string $connection)
    {
        // Preload metadata once (no N+1 queries)
        $billerMap = DB::connection($connection)
            ->table('billers')  
            ->pluck('name', 'id');

        $categoryMapType = DB::connection($connection)
            ->table('product_categories')
            ->pluck('type', 'id');

        $categoryMapName = DB::connection($connection)
            ->table('product_categories')
            ->pluck('name', 'id');

        $productMap = DB::connection($connection)
        ->table('products')
        ->pluck('name', 'id');

        return new ProductRequestNormalizer(
            $billerMap,
            $categoryMapType,
            $categoryMapName,
            $productMap
        );
    }

    protected function buildDiscoNormalizer(string $connection): DiscoNormalizer
    {
        $billerMap = DB::connection($connection)
            ->table('billers')
            ->pluck('name', 'id');

        return new DiscoNormalizer($billerMap);
    }
}
