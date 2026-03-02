<?php

namespace App\Services\Observability\Normalizers;

use App\Models\DataSource;
use App\Services\Observability\Normalizers\Contracts\NormalizerInterface;

class SmsNormalizer implements NormalizerInterface
{
    public function normalize($message, DataSource $source): array
    {
        $status = $this->normalizeStatus($message);

        return [
            'data_source_id' => $source->id,
            'source'         => 'bank_db',
            'client_code'    => strtoupper(str_replace('_sms', '', $source->code)),
            'service_type'   => 'SMS',
            'vendor_code'    => $message->network ?? null,
            'reference'      => 'msg_' . $message->id,
            'amount'         => null,
            'status'         => $status,
            'raw_status' => $message->dlr_status,
            'error_code'     => $status === 'FAILED' ? $message->dlr_status : null,
            'latency_ms'     => null,
            'raw_request'    => [
                'text'     => $message->text,
                'senderid' => $message->senderid,
                'pages'    => $message->pages,
            ],
            'raw_response'   => [
                'response'    => $message->response,
                'dlr_results' => $message->dlr_results,
            ],
            'occurred_at'    => $message->created_at,
        ];
    }

    protected function normalizeStatus($message): string
    {
        if (in_array($message->dlr_status, ['DELIVRD', 'SUCCESS'])) {
            return 'SUCCESS';
        }

        if (in_array($message->dlr_status, ['FAILED', 'UNDELIV', 'EXPIRED', 'REJECTD'])) {
            return 'FAILED';
        }

        return 'PENDING';
    }
}
