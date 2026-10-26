<?php

namespace BB\Providers;

use BB\Events\SettingUpdated;
use BB\Events\TrainingRecords\TrainingRecordCompletedEvent;
use BB\Events\TrainingRecords\TrainingRecordMarkedAsTrainerEvent;
use BB\Events\TrainingRecords\TrainingRecordRequestedEvent;
use BB\Listeners\DiscourseSyncSubscriber;
use BB\Listeners\EmailMemberAboutDeclinedPhoto;
use BB\Listeners\EmailMemberAboutTrustedStatus;
use BB\Listeners\ExtendMembership;
use BB\Listeners\RecordSettingChangeInAuditLog;
use BB\Listeners\Notifications\TrainingRecords\TrainingRecordCompletedListener;
use BB\Listeners\Notifications\TrainingRecords\TrainingRecordMarkedAsTrainerListener;
use BB\Listeners\Notifications\TrainingRecords\TrainingRecordRequestedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
	/**
	 * The event handler mappings for the application.
	 *
	 * @var array<string, array<int, string>>
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
		'BB\Events\MemberPhotoWasDeclined' => [
			EmailMemberAboutDeclinedPhoto::class,
		],
		'BB\Events\MemberGivenTrustedStatus' => [
			EmailMemberAboutTrustedStatus::class
		],
		TrainingRecordRequestedEvent::class => [
			TrainingRecordRequestedListener::class
		],
		TrainingRecordCompletedEvent::class => [
			TrainingRecordCompletedListener::class
		],
		TrainingRecordMarkedAsTrainerEvent::class => [
			TrainingRecordMarkedAsTrainerListener::class
		],
		SettingUpdated::class => [
			RecordSettingChangeInAuditLog::class
		]
	];

	/**
	 * The subscriber classes to register.
	 *
	 * @var array
	 */
	protected $subscribe = [
		DiscourseSyncSubscriber::class,
	];

	/**
	 * Register any other events for your application.
	 */
	public function boot()
	{
		parent::boot();
	}

	/**
	 * Listeners are registered explicitly above. Later Laravel versions turn
	 * discovery on by default, which would register them a second time.
	 */
	public function shouldDiscoverEvents()
	{
		return false;
	}
}
