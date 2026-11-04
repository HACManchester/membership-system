<?php

namespace BB\Support;

use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Payment::$status, which is cast to this enum. Besides the statuses the app
 * sets itself, a GoCardless payment's own status can be recorded as it stands,
 * so every status GoCardless documents for a payment is a case. A status from
 * GoCardless goes through fromGoCardless(), never from().
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Paid = 'paid';
    case Withdrawn = 'withdrawn';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
    /** Recording a payment GoCardless didn't accept, for an unexpected reason */
    case Error = 'error';

    // GoCardless's payment statuses (cancelled and failed are above)
    case PendingCustomerApproval = 'pending_customer_approval';
    /** Normalised to pending when the app records a payment it has just created */
    case PendingSubmission = 'pending_submission';
    case Submitted = 'submitted';
    case Confirmed = 'confirmed';
    case PaidOut = 'paid_out';
    case CustomerApprovalDenied = 'customer_approval_denied';
    case ChargedBack = 'charged_back';

    /**
     * The status for one GoCardless reports. A status GoCardless adds later
     * mustn't stop a payment being recorded mid-billing, so an unknown one is
     * reported and recorded as $fallback.
     */
    public static function fromGoCardless(string $status, self $fallback): self
    {
        $known = self::tryFrom($status);
        if ($known) {
            return $known;
        }

        $message = "Unknown GoCardless payment status \"{$status}\", recorded as {$fallback->value}";
        Log::warning($message);
        report(new RuntimeException($message));

        return $fallback;
    }

    /**
     * The label the payment lists show. Statuses without one show as stored.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending confirmation',
            self::PendingSubmission => 'Pending submission to members bank',
            self::Cancelled => 'Cancelled',
            self::Paid, self::Withdrawn => 'Paid',
            default => $this->value,
        };
    }
}
