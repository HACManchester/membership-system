<?php

namespace BB\Support;

/**
 * User::$status, which is cast to this enum: compare and write cases, and use
 * ->value only where a string is needed.
 */
enum MemberStatus: string
{
    case SettingUp = 'setting-up';
    case Active = 'active';
    case PaymentWarning = 'payment-warning';
    case Suspended = 'suspended';
    case Leaving = 'leaving';
    case Left = 'left';
    case Honorary = 'honorary';
    /** No longer set by the app, but still labelled if a member has it */
    case OnHold = 'on-hold';
}
