<?php namespace BB\Entities;

use BB\Helpers\MembershipPayments;
use BB\Support\SubscriptionChargeStatus;
use Carbon\Carbon;
use Database\Factories\SubscriptionChargeFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SubscriptionCharge
 *
 * @property integer $id
 * @property integer $user_id
 * @property Carbon  $charge_date
 * @property Carbon|null  $payment_date
 * @property integer $amount in pence
 * @property SubscriptionChargeStatus $status
 * @property-read User|null $user
 * @property-read string $status_label
 * @property-read string|null $payment_date_label
 * @property-read string $amount_label
 * @package BB\Entities
 */
#[UseFactory(SubscriptionChargeFactory::class)]
class SubscriptionCharge extends Model
{

    /** @use HasFactory<SubscriptionChargeFactory> */
    use HasFactory;

    protected $table = 'subscription_charge';


    protected $fillable = [
        'user_id', 'charge_date', 'amount', 'status'
    ];

    protected function casts(): array
    {
        return [
            'charge_date' => 'datetime',
            'payment_date' => 'datetime',
            'status' => SubscriptionChargeStatus::class,
            'amount' => 'integer',
        ];
    }



    /**
     * The GoCardless Idempotency-Key for collecting this charge from $mandateId.
     * GoCardless creates at most one payment per key (keys are honoured for at
     * least 30 days) and answers a repeat with the payment it already made, so
     * billing the same charge twice can't take the money twice. The member, date
     * and mandate are part of the key so that a charge ID reused after a
     * database restore can't pick up another charge's payment.
     */
    public function goCardlessIdempotencyKey(?string $mandateId): string
    {
        return sprintf('subscription-charge-%d-user-%d-%s-%s', $this->id, $this->user_id, $this->charge_date->format('Y-m-d'), $mandateId);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Not yet sent for collection: what a member still owes.
     */
    #[Scope]
    protected function outstanding(Builder $query): void
    {
        $query->whereIn('status', [SubscriptionChargeStatus::Pending, SubscriptionChargeStatus::Due]);
    }

    /**
     * Not yet paid or cancelled, including charges whose payment is on its way.
     */
    #[Scope]
    protected function inFlight(Builder $query): void
    {
        $query->whereIn('status', [SubscriptionChargeStatus::Pending, SubscriptionChargeStatus::Due, SubscriptionChargeStatus::Processing]);
    }

    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', SubscriptionChargeStatus::Pending);
    }

    #[Scope]
    protected function due(Builder $query): void
    {
        $query->where('status', SubscriptionChargeStatus::Due);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => $this->status->label());
    }

    /**
     * The date the charge was paid, or null until it has been.
     *
     * @return Attribute<string|null, never>
     */
    protected function paymentDateLabel(): Attribute
    {
        return Attribute::get(fn () => $this->status === SubscriptionChargeStatus::Paid ? $this->payment_date?->toFormattedDateString() : null);
    }

    /**
     * The amount once it is being collected, and blank before then.
     *
     * @return Attribute<string, never>
     */
    protected function amountLabel(): Attribute
    {
        return Attribute::get(fn () => in_array($this->status, [SubscriptionChargeStatus::Processing, SubscriptionChargeStatus::Paid], true) ? MembershipPayments::formatPrice($this->amount) : '');
    }
}
