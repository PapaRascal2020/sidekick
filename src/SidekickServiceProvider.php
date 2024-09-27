<?php

namespace PapaRascalDev\Sidekick;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use PapaRascalDev\Sidekick\Console\InstallCommand;
use PapaRascalDev\Sidekick\Models\SidekickConversation as SidekickConversationModel;

/**
 * Sidekick Service Provider
 */

class SidekickServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {

        $this->commands([
            InstallCommand::class,
        ]);

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
    public function register(): void
    {
    }
}
