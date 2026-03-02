<?php

namespace App\Services\Observability;

use App\Models\DataSource;
use App\Models\Transaction;
use App\Models\BankMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmsIngestionService
{
    public function ingest(DataSource $source): void
    {
        Log::info('SMS INGEST START', [
            'source_code' => $source->code,
            'cursor'      => $source->cursor,
        ]);

        $config   = $this->validateSource($source);
        $messages = $this->fetchMessages($source, $config);

        if ($messages->isEmpty()) {
            Log::info('SMS INGEST: no new messages', ['source_code' => $source->code]);
            $source->last_polled_at = now();
            $source->save();
            return;
        }

        DB::transaction(function () use ($source, $messages, $config) {
            foreach ($messages as $message) {
                $this->recordTransaction($source, $message);
            }

            $source->cursor         = $messages->last()->{$config['id_column']};
            $source->last_polled_at = now();
            $source->save();
        });

        Log::info('SMS INGEST COMPLETE', [
            'source_code'   => $source->code,
            'messages'      => $messages->count(),
            'cursor_now'    => $source->cursor,
        ]);
    }

    // -------------------------------------------------------------------------

    protected function validateSource(DataSource $source): array
    {
        if ($source->type !== 'database') {
            throw new \InvalidArgumentException(
                "DataSource [{$source->code}] is not type 'database', got '{$source->type}'"
            );
        }

        $config = $source->config;

        foreach (['connection', 'table', 'id_column'] as $key) {
            if (empty($config[$key])) {
                throw new \InvalidArgumentException(
                    "DataSource [{$source->code}] missing config key: {$key}"
                );
            }
        }

        return $config;
    }

    // -------------------------------------------------------------------------

                protected function fetchMessages(DataSource $source, array $config)
            {
                $cursor = $source->cursor ?? 0;

                // Define reconciliation window (adjust based on real DLR delay behavior)
                $windowStart = now()->subMinutes(15);

                return (new BankMessage)
                    ->useConnection($config['connection'])
                    ->useTable($config['table'])
                    ->where(function ($query) use ($config, $cursor, $windowStart) {
                        $query->where($config['id_column'], '>', $cursor)
                            ->orWhere('created_at', '>=', $windowStart);
                    })
                    ->orderBy($config['id_column'])
                    ->limit(1000)
                    ->get();
            }



    // -------------------------------------------------------------------------

    protected function normalizeStatus(BankMessage $message): string
    {
        if (in_array($message->dlr_status, ['DELIVRD', 'SUCCESS'])) {
            return 'SUCCESS';
        }

        if (in_array($message->dlr_status, ['FAILED', 'UNDELIV', 'EXPIRED', 'REJECTD'])) {
            return 'FAILED';
        }

        return 'PENDING';
    }

    protected function resolveServiceType(BankMessage $message): string
    {
        // OTP senders are typically short numeric codes or contain OTP/VERIFY
        // Adjust these patterns to match what your actual bank senderids look like
        $sender = strtoupper(trim($message->senderid ?? ''));

        if (
            is_numeric($sender) && strlen($sender) <= 6 ||
            str_contains($sender, 'OTP') ||
            str_contains($sender, 'VERIF') ||
            str_contains($sender, 'AUTH')
        ) {
            return 'SMS_OTP';
        }

        return 'SMS_TRANS';
    }

    // -------------------------------------------------------------------------

    protected function recordTransaction(DataSource $source, BankMessage $message): void
    {
        $status      = $this->normalizeStatus($message);
        $serviceType = $this->resolveServiceType($message);
        $reference   = 'msg_' . $message->id;

        Transaction::updateOrCreate(
            [
                'reference' => $reference,   // dedup key — never changes
            ],
            [
                'source'       => 'bank_db',
                'client_code'  => strtoupper(str_replace('_sms', '', $source->code)),
                'service_type' => $serviceType,
                'vendor_code'  => $message->network ?? null,
                'endpoint'     => null,
                'phone'        => $message->msisdn,
                'status'       => $status,
                'error_code'   => $status === 'FAILED' ? $message->dlr_status : null,
                'latency_ms'   => null,
                'raw_request'  => [
                    'text'     => $message->text,
                    'senderid' => $message->senderid,
                    'pages'    => $message->pages,
                ],
                'raw_response' => [
                    'response'    => $message->response,
                    'dlr_results' => $message->dlr_results,
                ],
                'occurred_at'  => $message->created_at,
            ]
        );
    }
}