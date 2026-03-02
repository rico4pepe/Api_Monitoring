<?php

namespace App\Services\Observability;

use App\Models\DataSource;
use App\Models\Transaction;
use App\Models\BankMessage;

class SmsIngestionService
{
    public function ingest(DataSource $source): void
    {
        logger()->info('SMS INGEST CONFIG', [
            'source_code' => $source->code,
            'config' => $source->config,
            'cursor' => $source->cursor,
        ]);
        $config = $this->validateSource($source);

        $messages = $this->fetchMessages($source, $config);

        foreach ($messages as $message) {
            $this->recordTransaction($source, $message);
            $source->cursor = $message->{$config['id_column']};
        }

        $source->last_polled_at = now();
        $source->save();
    }



    protected function validateSource(DataSource $source): array
    {
        if ($source->type !== 'database') {
            throw new \InvalidArgumentException('Invalid data source type');
        }

        $config = $source->config;

        foreach (['connection', 'table', 'id_column'] as $key) {
            if (!isset($config[$key])) {
                throw new \InvalidArgumentException("Missing config key: {$key}");
            }
        }

        return $config;
    }

    protected function fetchMessages(DataSource $source, array $config)
        {
            return (new BankMessage)
                ->useConnection($config['connection'])
                ->useTable($config['table'])
                ->where(
                    $config['id_column'],
                    '>',
                    $source->cursor ?? 0
                )
                ->orderBy($config['id_column'])
                ->limit(500)
                ->get();
        }

        protected function normalizeStatus(BankMessage $message): string
            {
                 if (in_array($message->dlr_status, ['DELIVRD', 'SUCCESS'])) {
                    return 'SUCCESS';
                }

                    // 2. Explicit failure
                if (in_array($message->dlr_status, [
                    'FAILED',
                    'UNDELIV',
                    'EXPIRED',
                    'REJECTD'
                ])) {
                    return 'FAILED';
                }

                  // 3. Everything else is pending
                    return 'PENDING';
            }

            protected function recordTransaction(
                DataSource $source,
                BankMessage $message
            ): void {
                Transaction::updateOrCreate([
                    'source'       => 'bank_db',
                    'client_code'  => strtoupper(str_replace('_sms', '', $source->code)),
                    'service_type' => 'SMS',
                    'vendor_code'  => $message->network,
                    'endpoint'     => null,
                    'reference'    => 'msg_' . $message->id,
                    'phone'        => $message->msisdn,
                    'status'       => $this->normalizeStatus($message),
                    'error_code'   => $message->dlr_status,
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
                ]);
            }




}
