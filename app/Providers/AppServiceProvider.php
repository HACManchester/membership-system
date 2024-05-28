<?php

namespace BB\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		if (!$this->app->isLocal()) {
			// TODO: What's this doing? 🤔
			$this->app['request']->server->set('HTTPS', true);
		}
	}

	/**
	 * Register any application services.
	 *
	 * This service provider is a great spot to register your various container
	 * bindings with the application. As you can see, we are registering our
	 * "Registrar" implementation here. You can add your own bindings too!
	 *
	 * @return void
	 */
	public function register()
	{
		// We're running a higher PHP version than Laravel 5.2 supports. Temporarily ignore certain notices.
		if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
			error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
		}

		$this->app->bind(
			'Illuminate\Contracts\Auth\Registrar',
			'BB\Services\Registrar'
		);
		if ($this->app->environment() !== 'production') {
			$this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
		}
	}
}
