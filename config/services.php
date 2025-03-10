<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Third Party Services
	|--------------------------------------------------------------------------
	|
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
	|
	*/

	'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
	],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

	'telegram-bot-api' => [
		'enabled' => !env('TELEGRAM_NOTIFICATIONS_DISABLED', false),
		'token' => env('TELEGRAM_BOT_KEY', 'YOUR BOT TOKEN HERE')
	],

	// Would put this in config/sentry.php, but its values are passed to
	// sentry/sentry-laravel and it errors on unrecognised keys
	'sentry' => [
		'browser_dsn' => env('SENTRY_BROWSER_DSN'),
	],

];
