<?php namespace BB\Entities;

use Carbon\Carbon;
use Database\Factories\SubscriptionChargeFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laracasts\Presenter\PresentableTrait;

/**
 * Class SubscriptionCharge
 *
 * @property integer $id
 * @property integer $user_id
 * @property Carbon  $charge_date
 * @property Carbon|null  $payment_date
 * @property integer $amount
 * @property string  $status
 * @property-read User|null $user
 * @package BB\Entities
 */
#[UseFactory(SubscriptionChargeFactory::class)]
class SubscriptionCharge extends Model
{

    /** @use HasFactory<SubscriptionChargeFactory> */
    use HasFactory, PresentableTrait;

    protected $presenter = 'BB\Presenters\SubscriptionChargePresenter';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'subscription_charge';


    /**
     * Fillable fields
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'charge_date', 'amount', 'status'
    ];

    protected $attributes = [
        'payment_date' => '0000-00-00 00:00:00'
    ];

    protected $casts = [
        'charge_date' => 'datetime',
        'payment_date' => 'datetime',
    ];



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

} 