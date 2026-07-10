<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Common;

enum StatusCode: string
{
    case OK = 'OK';
    case FAILED = 'FAILED';
    case APPROVED = 'APPROVED';
    case APPROVED_PARTIAL = 'APPROVED_PARTIAL';
    case REJECTED = 'REJECTED';
    case PENDING = 'PENDING';
    case PENDING_VALIDATION = 'PENDING_VALIDATION';
    case REFUNDED = 'REFUNDED';
    case ERROR = 'ERROR';
    case UNKNOWN = 'UNKNOWN';

    public static function fromString(?string $value): self
    {
        if ($value === null) {
            return self::UNKNOWN;
        }
        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }
        return self::UNKNOWN;
    }
}
