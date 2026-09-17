<?php

namespace Flightsadmin\LivewireCrud;;

use Flightsadmin\LivewireCrud\Commands\LivewireCrudGenerator;
use Flightsadmin\LivewireCrud\Commands\LivewireInstall;
use Illuminate\Support\ServiceProvider;

class LivewireCrudServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('livewire-crud.php'),
            ], 'config');
            $this->commands([
				LivewireCrudGenerator::class,
				LivewireInstall::class,
			]);
        }
    }
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'livewire-crud');
        $this->app->singleton('livewire-crud', function () {
            return new LivewireCrud;
        });
    }
}
