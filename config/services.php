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
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
	],

	'mandrill' => [
		'secret' => '',
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
		'token' => env('TELEGRAM_BOT_KEY', 'YOUR BOT TOKEN HERE')
	],

	// Would put this in config/sentry.php, but its values are passed to
	// sentry/sentry-laravel and it errors on unrecognised keys
	'sentry' => [
		'browser_dsn' => env('SENTRY_BROWSER_DSN'),
	],

];
