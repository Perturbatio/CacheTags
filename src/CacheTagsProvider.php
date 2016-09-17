<?php

namespace Perturbatio\CacheTags;

use Illuminate\Support\ServiceProvider;

class CacheTagsProvider extends ServiceProvider {
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        $configPath = __DIR__ . '/config/cachetags.php';
        $this->publishes([$configPath => $this->getConfigPath()], 'config');
        CacheTags::registerBladeDirectives();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        $configPath = __DIR__ . '/config/cachetags.php';
        $this->mergeConfigFrom($configPath, 'cachetags');
        //
        $this->app->singleton(CacheTags::class, function ( $app ) {
            return new CacheTags(cache());
        });
    }

    /**
     * Get the config path
     *
     * @return string
     */
    protected function getConfigPath() {
        return config_path('cachetags.php');
    }

    /**
     * Publish the config file
     *
     * @param  string $configPath
     */
    protected function publishConfig( $configPath ) {
        $this->publishes([$configPath => config_path('cachetags.php')], 'config');
    }

}
