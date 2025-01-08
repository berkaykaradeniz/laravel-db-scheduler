<?php

namespace BerkayKaradeniz\LaravelDbScheduler\Providers;

use Illuminate\Support\ServiceProvider;

class SchedulerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/scheduler.php', 'scheduler'
        );
    }

    public function boot()
    {
        // Routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Migrations
        $this->loadMigrationsFrom(__DIR__.'/../Migrations');

        // Config
        $this->publishes([
            __DIR__.'/../config/scheduler.php' => config_path('scheduler.php'),
        ], 'config');

        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \BerkayKaradeniz\LaravelDbScheduler\Console\Commands\RunScheduledJobs::class,
            ]);
        }
    }
} 