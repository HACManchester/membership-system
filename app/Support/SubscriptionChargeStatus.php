<?php

namespace BB\Support;

/**
 * SubscriptionCharge::$status, which is cast to this enum (the column is a
 * MySQL ENUM of the same values).
 */
enum SubscriptionChargeStatus: string
{
    case Pending = 'pending';
    case Due = 'due';
    case Processing = 'processing';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Due => 'Due',
            self::Processing => 'Processing',
            self::Paid => 'Paid',
            self::Cancelled => 'Cancelled',
        };
    }
}
