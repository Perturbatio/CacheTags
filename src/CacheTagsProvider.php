<?php

namespace Perturbatio\CacheTags;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class CacheTagsProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		CacheTags::registerBladeDirectives();
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$configPath = __DIR__ . '/config/cachetags.php';
		$this->mergeConfigFrom($configPath, 'debugbar');
		//
		$this->app->singleton(CacheTags::class, function ($app) {
			return new CacheTags(cache());
		});
	}
}
