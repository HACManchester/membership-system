<?php

namespace BB\Providers;

use Illuminate\Database\Eloquent\Model;
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

		// Mass-assigning an attribute that isn't fillable throws outside production
		// instead of being silently dropped, so a write that does nothing shows up
		Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
	}

	public function register()
	{
		if ($this->app->environment() !== 'production') {
			$this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
		}
	}
}
