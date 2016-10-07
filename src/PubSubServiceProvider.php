<?php

namespace GenTux\PubSub;

use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;

class PubSubServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config.php', 'pubsub');

        $this->app->singleton(
            \GenTux\PubSub\Contracts\PubSub::class,
            function ($app) {
                $config = $app->make(Repository::class);
                $driver = $config->get('pubsub.driver', 'google');
                $driver = ucfirst(camel_case($driver));

                return $app->make("\\GenTux\\PubSub\\Drivers\\{$driver}\\PubSub");
            }
        );
    }
}
