<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Third Party Services
	|--------------------------------------------------------------------------
	|
	| This file is for storing the credentials for third party services such
	| as Stripe, Mailgun, Mandrill, and others. This file provides a sane
	| default location for this type of information, allowing packages
	| to have a conventional place to find your various credentials.
	|
	*/

	'mailgun' => [
		'domain' => '',
		'secret' => '',
	],

	'mandrill' => [
		'secret' => '',
	],

	'ses' => [
		'key' => '',
		'secret' => '',
		'region' => 'us-east-1',
	],

	'stripe' => [
		'model'  => 'BB\Entities\User',
		'key' => '',
		'secret' => '',
	],

	'telegram-bot-api' => [
		'token' => env('TELEGRAM_BOT_KEY', 'YOUR BOT TOKEN HERE')
	],

	// Would put this in config/sentry.php, but its values are passed to
	// sentry/sentry-laravel and it errors on unrecognised keys
	'sentry' => [
		'browser_dsn' => env('SENTRY_BROWSER_DSN'),
	],

];
