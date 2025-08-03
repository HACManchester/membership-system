<?php

namespace BB\Entities;


use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;

class Payment extends Model
{
    use PresentableTrait;

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
     * @var array
     */
    protected $hidden = array();

    /**
     * Fillable fields
     *
     * @var array
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

    protected $casts = [
        'paid_at' => 'datetime',
    ];


    protected $presenter = 'BB\Presenters\PaymentPresenter';


    public function user()
    {
        return $this->belongsTo('\BB\Entities\User');
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
