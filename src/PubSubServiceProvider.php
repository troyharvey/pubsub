<?php

namespace GenTux\PubSub;

use GenTux\PubSub\Exceptions\PubSubDriverNotDefinedException;
use Illuminate\Contracts\Config\Repository;
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
        $this->mergeConfigFrom(
            realpath(dirname(__FILE__)) . '/config.php',
            'pubsub'
        );

        $this->app->singleton(
            Contracts\PubSub::class,
            function ($app) {
                /** @var Repository $config */
                $config = $app->make('config');
                $driver = $config->get('pubsub.driver');

                if (empty($driver)) {
                    throw new PubSubDriverNotDefinedException();
                }

                $driver = ucfirst(camel_case($driver));

                return $app->make("\\GenTux\\PubSub\\Drivers\\{$driver}\\PubSub");
            }
        );
    }
}
