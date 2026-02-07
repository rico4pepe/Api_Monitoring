<?php

namespace App\Services\DeliveryState;

use Carbon\CarbonImmutable;

final class DeliveryStateResolver
{
    private int $pendingSlaMinutes;

    public function __construct(int $pendingSlaMinutes = 5)
    {
        $this->pendingSlaMinutes = $pendingSlaMinutes;
    }

    /**
     * Resolve the true delivery state of a transaction snapshot.
     *
     * @param array $input  Raw transaction snapshot
     * @param CarbonImmutable $now  Evaluation time (injected)
     */
    public function resolve(array $input, CarbonImmutable $now): DeliveryStateResult
    {
        $status       = $input['status']        ?? null;
        $occurredAt   = $this->toCarbon($input['occurred_at'] ?? null);
        $updatedAt    = $this->toCarbon($input['updated_at']  ?? null);
        $dlrStatus    = $input['dlr_status']     ?? null;
        $rawResponse  = $input['raw_response']   ?? null;
        $errorCode    = $input['error_code']     ?? null;

        /* =========================
         | 1. Strongest signal: DLR
         |========================= */

        if ($dlrStatus !== null) {
            switch ($dlrStatus) {
                case 'DELIVRD':
                    return DeliveryStateResult::delivered($updatedAt ?? $now);

                case 'REJECTD':
                    return DeliveryStateResult::rejected(
                        $updatedAt ?? $now,
                        $errorCode,
                        $this->extractReason($rawResponse)
                    );

                case 'UNDELIV':
                case 'EXPIRED':
                    return DeliveryStateResult::undelivered(
                        $updatedAt ?? $now,
                        $errorCode,
                        $this->extractReason($rawResponse)
                    );

                case 'PENDING':
                    return $this->resolvePending($occurredAt, $now);
            }
        }

        /* =========================
         | 2. Fallback to coarse status
         |========================= */

        if ($status === 'SUCCESS') {
            return DeliveryStateResult::delivered($updatedAt ?? $now);
        }

        if ($status === 'FAILED') {
            return $this->classifyFailure(
                $updatedAt ?? $now,
                $errorCode,
                $rawResponse
            );
        }

        // Default: pending
        return $this->resolvePending($occurredAt, $now);
    }

    /* =========================
     | Failure classification
     |========================= */

    private function classifyFailure(
        CarbonImmutable $resolvedAt,
        ?string $errorCode,
        $rawResponse
    ): DeliveryStateResult {
        if ($this->isRejectedError($errorCode, $rawResponse)) {
            return DeliveryStateResult::rejected(
                $resolvedAt,
                $errorCode,
                $this->extractReason($rawResponse)
            );
        }

        // Conservative default
        return DeliveryStateResult::undelivered(
            $resolvedAt,
            $errorCode,
            $this->extractReason($rawResponse)
        );
    }

    /* =========================
     | Pending resolution
     |========================= */

    private function resolvePending(
        ?CarbonImmutable $occurredAt,
        CarbonImmutable $now
    ): DeliveryStateResult {
        $isAged = false;

        if ($occurredAt !== null) {
            $ageMinutes = $occurredAt->diffInMinutes($now);
            $isAged = $ageMinutes > $this->pendingSlaMinutes;
        }

        return DeliveryStateResult::pending($now, $isAged);
    }

    /* =========================
     | Rejection heuristics
     |========================= */

    private function isRejectedError(?string $errorCode, $rawResponse): bool
    {
        if ($errorCode === null && $rawResponse === null) {
            return false;
        }

        $knownRejectionCodes = [
            'DND',
            'INVALID_MSISDN',
            'CONTENT_BLOCKED',
            'CONTENT_RESTRICTED',
        ];

        if ($errorCode && in_array($errorCode, $knownRejectionCodes, true)) {
            return true;
        }

        $reason = $this->extractReason($rawResponse);

        if ($reason === null) {
            return false;
        }

        $rejectionKeywords = [
            'dnd',
            'blocked',
            'restricted',
            'invalid',
            'barred',
        ];

        foreach ($rejectionKeywords as $keyword) {
            if (str_contains(strtolower($reason), $keyword)) {
                return true;
            }
        }

        return false;
    }

    /* =========================
     | Helpers
     |========================= */

    private function extractReason($rawResponse): ?string
    {
        if (is_array($rawResponse)) {
            return $rawResponse['description'] ?? null;
        }

        if (is_string($rawResponse)) {
            return $rawResponse;
        }

        return null;
    }

    private function toCarbon($value): ?CarbonImmutable
    {
        if ($value instanceof CarbonImmutable) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return CarbonImmutable::parse($value);
    }
}
