<?php

namespace App\Services\DeliveryState;

use Carbon\CarbonImmutable;

final class DeliveryStateResult
{
    public readonly string $deliveryState;
    public readonly bool $isTerminal;
    public readonly bool $isAgedPending;
    public readonly ?string $failureClass;
    public readonly CarbonImmutable $resolvedAt;
    public readonly ?string $reasonCode;
    public readonly ?string $reasonDescription;

    private function __construct(
        string $deliveryState,
        bool $isTerminal,
        bool $isAgedPending,
        ?string $failureClass,
        CarbonImmutable $resolvedAt,
        ?string $reasonCode = null,
        ?string $reasonDescription = null
    ) {
        $this->deliveryState = $deliveryState;
        $this->isTerminal = $isTerminal;
        $this->isAgedPending = $isAgedPending;
        $this->failureClass = $failureClass;
        $this->resolvedAt = $resolvedAt;
        $this->reasonCode = $reasonCode;
        $this->reasonDescription = $reasonDescription;
    }

    /* =========================
     | Named constructors
     |========================= */

    public static function delivered(
        CarbonImmutable $resolvedAt
    ): self {
        return new self(
            deliveryState: 'delivered',
            isTerminal: true,
            isAgedPending: false,
            failureClass: null,
            resolvedAt: $resolvedAt
        );
    }

    public static function rejected(
        CarbonImmutable $resolvedAt,
        ?string $reasonCode = null,
        ?string $reasonDescription = null
    ): self {
        return new self(
            deliveryState: 'rejected',
            isTerminal: true,
            isAgedPending: false,
            failureClass: 'rejected',
            resolvedAt: $resolvedAt,
            reasonCode: $reasonCode,
            reasonDescription: $reasonDescription
        );
    }

    public static function undelivered(
        CarbonImmutable $resolvedAt,
        ?string $reasonCode = null,
        ?string $reasonDescription = null
    ): self {
        return new self(
            deliveryState: 'undelivered',
            isTerminal: true,
            isAgedPending: false,
            failureClass: 'undelivered',
            resolvedAt: $resolvedAt,
            reasonCode: $reasonCode,
            reasonDescription: $reasonDescription
        );
    }

    public static function pending(
        CarbonImmutable $resolvedAt,
        bool $isAgedPending
    ): self {
        return new self(
            deliveryState: 'pending',
            isTerminal: false,
            isAgedPending: $isAgedPending,
            failureClass: null,
            resolvedAt: $resolvedAt
        );
    }
}
