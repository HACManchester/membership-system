<?php

namespace BB\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
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

		JsonResource::withoutWrapping();
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
		if ($this->app->environment() !== 'production') {
			$this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
		}
	}
}
