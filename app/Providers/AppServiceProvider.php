<?php

namespace BB\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

	public function boot()
	{
		if (!$this->app->isLocal()) {
			// TODO: What's this doing? 🤔
			$this->app->make('request')->server->set('HTTPS', true);
		}

		JsonResource::withoutWrapping();

		// Laravel 8 made Tailwind the default paginator markup; the Blade pages use Bootstrap
		Paginator::useBootstrap();
	}

	public function register()
	{
		if ($this->app->environment() !== 'production') {
			$this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
		}
	}
}
