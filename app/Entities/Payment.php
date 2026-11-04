<?php

namespace BB\Entities;

use BB\Support\PaymentStatus;
use Carbon\Carbon;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $reason
 * @property string|null $reference
 * @property string $source
 * @property string|null $source_id
 * @property float $amount in pounds
 * @property float $fee in pounds
 * @property float $amount_minus_fee in pounds
 * @property PaymentStatus $status
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $reason_label
 * @property-read string $status_label
 * @property-read string $method_label
 */
#[UseFactory(PaymentFactory::class)]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'source',
        'source_id',
        'user_id',
        'amount',
        'fee',
        'amount_minus_fee',
        'status',
        'reason',
        'created_at',
        'reference',
        'paid_at',
    ];


    protected $attributes = [
        'status' => PaymentStatus::Pending->value,
        'fee' => 0,
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'status' => PaymentStatus::class,
        ];
    }


    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Scope]
    protected function subscription(Builder $query): void
    {
        $query->whereReason('subscription');
    }

    /**
     * @return Attribute<string, never>
     */
    protected function reasonLabel(): Attribute
    {
        return Attribute::get(fn () => match ($this->reason) {
            'subscription' => 'Subscription',
            'unknown' => 'Unknown',
            'induction' => 'Equipment Access Fee',
            'door-key' => 'Key Deposit',
            'storage-box' => 'Storage Box Deposit',
            'balance' => 'Balance Top Up',
            'equipment-fee' => 'Equipment Costs',
            'withdrawal' => 'Withdrawal',
            'consumables' => 'Consumables (' . $this->reference . ')',
            'donation' => 'Donation',
            default => $this->reason,
        });
    }

    /**
     * @return Attribute<string, never>
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => $this->status->label());
    }

    /**
     * How the payment was made, e.g. "Direct Debit".
     *
     * @return Attribute<string, never>
     */
    protected function methodLabel(): Attribute
    {
        return Attribute::get(fn () => match ($this->source) {
            'gocardless', 'gocardless-variable' => 'Direct Debit',
            'standing-order' => 'Standing Order',
            'manual' => 'Manual',
            'cash' => 'Cash' . ($this->source_id ? ' (' . $this->source_id . ')' : ''),
            'other' => 'Other',
            'balance' => 'BB Balance',
            'reimbursement' => 'Reimbursement',
            default => $this->source,
        });
    }

    public static function getPaymentReasons()
    {
        return [
            'subscription'  => 'Subscription',
            'induction'     => 'Equipment Access Fee',
            'snackspace'    => 'Snackspace',
            'snackspace-kiosk'    => 'Snackspace Kiosk (old)',
            'balance'       => 'Balance',
            'Laser Materials' => 'Laser Materials',
            'equipment-fee' => 'Equipment Costs',
            'Fob' => 'Fob',
            'Heat Press Items' => 'Heat Press Items',
            '3D Printer Filament' => '3D Printer Filament',
            'Misc Materials' => 'Misc Materials',
        ];
    }
}
