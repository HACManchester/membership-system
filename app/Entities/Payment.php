<?php

namespace BB\Entities;


use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $reason_label
 * @property-read string $status_label
 * @property-read string $method_label
 */
#[UseFactory(PaymentFactory::class)]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PENDING_SUBMISSION = 'pending_submission';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PAID = 'paid';
    const STATUS_WITHDRAWN = 'withdrawn';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payments';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var list<string>
     */
    protected $hidden = array();

    /**
     * Fillable fields
     *
     * @var list<string>
     */
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
        'status' => 'pending',
        'fee' => 0,
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }


    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSubscription($query)
    {
        return $query->whereReason('subscription');
    }

    /**
     * Allow the ref property to be used instead of reference.
     *
     * @return string
     */
    public function getRefAttribute()
    {
        return $this->attributes['reference'];
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
        return Attribute::get(fn () => match ($this->status) {
            self::STATUS_PENDING => 'Pending confirmation',
            self::STATUS_PENDING_SUBMISSION => 'Pending submission to members bank',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_PAID, self::STATUS_WITHDRAWN => 'Paid',
            default => $this->status,
        });
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
