<?php

namespace BB\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A row of the member's balance history: money taken from the balance is a
 * debit, a top up is a credit.
 *
 * @mixin \BB\Entities\Payment
 */
class BalancePaymentResource extends JsonResource
{
    public function toArray($request)
    {
        $direction = match (true) {
            $this->source === 'balance' => 'debit',
            $this->reason === 'balance' => 'credit',
            default => null,
        };

        // payments.amount is in pounds
        $amount = '£' . number_format((float) $this->amount, 2);

        return [
            'id' => $this->id,
            'reason' => $this->reason_label,
            'method' => $this->method_label,
            'date' => $this->created_at?->toFormattedDateString(),
            'direction' => $direction,
            'amount' => match ($direction) {
                'debit' => '-' . $amount,
                'credit' => $amount,
                default => null,
            },
            'status' => $this->status_label,
        ];
    }
}
