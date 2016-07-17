<?php

namespace GenTux\GooglePubSub;

use GenTux\GooglePubSub\Console\Commands\AddGoogleSite;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(
            [
                __DIR__.'/config.php' => config_path('pubsub.php'),
            ]
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            AddGoogleSite::class,
        ]);
        
        $this->mergeConfigFrom(__DIR__.'/config.php', 'queue.connections.pubsub');
    }
}
