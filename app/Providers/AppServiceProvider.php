<?php namespace BB\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider {

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
		$this->app->bind(
			'Illuminate\Contracts\Auth\Registrar',
			'BB\Services\Registrar'
		);
		
		if ($this->app->isLocal()) {
			/**
			 * This was breaking live (:
			 * 
			 * TODO: Properly re-install ide-helper as per 2.4.2 instructions (last compatible with Laravel 5.1)
			 * @see https://github.com/barryvdh/laravel-ide-helper/tree/v2.4.3#install
			 * 
			 * Will also need a composer install on live, which hasn't been done in a while. Need to carefully plan.
			 */
			// $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
		}

	}
}
