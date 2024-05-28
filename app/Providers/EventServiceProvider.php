<?php

namespace BB\Providers;

use BB\Events\Inductions\InductionCompletedEvent;
use BB\Events\Inductions\InductionMarkedAsTrainerEvent;
use BB\Events\Inductions\InductionRequestedEvent;
use BB\Events\MemberBalanceChanged;
use BB\Events\SubscriptionPayment;
use BB\Listeners\AddApprovedExpenseToBalance;
use BB\Listeners\EmailMemberAboutApprovedExpense;
use BB\Listeners\EmailMemberAboutDeclinedExpense;
use BB\Listeners\EmailMemberAboutDeclinedPhoto;
use BB\Listeners\EmailMemberAboutFailedSubscriptionPayment;
use BB\Listeners\EmailMemberAboutFailedSubscriptionPaymentGoingToBackup;
use BB\Listeners\EmailMemberAboutTrustedStatus;
use BB\Listeners\EmailboardAboutExpense;
use BB\Listeners\ExtendMembership;
use BB\Listeners\MemberBalanceSubscriber;
use BB\Listeners\Notifications\Inductions\InductionCompletedListener;
use BB\Listeners\Notifications\Inductions\InductionMarkedAsTrainerListener;
use BB\Listeners\Notifications\Inductions\InductionRequestedListener;
use BB\Listeners\RecalculateMemberBalance;
use BB\Listeners\RecordMemberActivity;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
		'payment.create' => [
			'BB\Handlers\PaymentEventHandler@onCreate',
		],
		'payment.delete' => [
			'BB\Handlers\PaymentEventHandler@onDelete',
		],
		'payment.cancelled' => [
			'BB\Handlers\PaymentEventHandler@onCancel',
		],
		'payment.paid' => [
			'BB\Handlers\PaymentEventHandler@onPaid',
		],
		'BB\Events\SubscriptionChargePaid' => [
			ExtendMembership::class
		],
		'sub-charge.processing' => [
			'BB\Handlers\SubChargeEventHandler@onProcessing',
		],
		'sub-charge.payment-failed' => [
			'BB\Handlers\SubChargeEventHandler@onPaymentFailure',
		],
		'BB\Events\NewExpenseSubmitted' => [
			EmailboardAboutExpense::class,
		],
		'BB\Events\ExpenseWasApproved' => [
			EmailMemberAboutApprovedExpense::class,
			AddApprovedExpenseToBalance::class,
		],
		'BB\Events\ExpenseWasDeclined' => [
			EmailMemberAboutDeclinedExpense::class,
		],
		'BB\Events\MemberPhotoWasDeclined' => [
			EmailMemberAboutDeclinedPhoto::class,
		],
		'BB\Events\MemberActivity' => [
			RecordMemberActivity::class,
		],
		'BB\Events\MemberGivenTrustedStatus' => [
			EmailMemberAboutTrustedStatus::class
		],
		'BB\Events\NewMemberNotification' => [],
		SubscriptionPayment\FailedInsufficientFunds::class => [
			EmailMemberAboutFailedSubscriptionPayment::class
		],
		SubscriptionPayment\InsufficientFundsTryingDirectDebit::class => [
			EmailMemberAboutFailedSubscriptionPaymentGoingToBackup::class
		],
		InductionRequestedEvent::class => [
			InductionRequestedListener::class
		],
		InductionCompletedEvent::class => [
			InductionCompletedListener::class
		],
		InductionMarkedAsTrainerEvent::class => [
			InductionMarkedAsTrainerListener::class
		],
	];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        MemberBalanceSubscriber::class,
    ];

	/**
	 * Register any other events for your application.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
	 * @return void
	 */
	public function boot()
	{
		parent::boot();
	}
}
