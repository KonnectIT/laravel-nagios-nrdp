<?php

namespace KonnectIT\LaravelNagiosNrdp;

use Illuminate\Support\ServiceProvider;

class LaravelNagiosNrdpServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravel-nagios-nrdp.php' => config_path('laravel-nagios-nrdp.php'),
            ], 'config');

            /*
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'skeleton');

            $this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/skeleton'),
            ], 'views');
            */
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-nagios-nrdp.php', 'laravel-nagios-nrdp');

//        $this->app->bind(NagiosNrdp::class);

        $this->app->alias(NagiosNrdp::class, 'laravel-nagios-nrdp');
    }
}
