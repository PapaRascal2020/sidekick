<?php

namespace PapaRascalDev\Sidekick;

use Illuminate\Support\ServiceProvider;

class SidekickServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('sidekick.php'),
            ], 'config');
        }

        $this->initializeMigrations();
        $this->initializeMigrationPublishing();
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function initializeMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function initializeMigrationPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'sidekick-migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'sidekick');
    }
}
