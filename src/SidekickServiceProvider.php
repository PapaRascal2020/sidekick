<?php

namespace PapaRascalDev\Sidekick;

use Illuminate\Support\ServiceProvider;

class SidekickServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('sidekick.php'),
            ], 'config');
        }

        $this->initializeMigrations();
        $this->initializeMigrationPublishing();

//        $this->initializeControllers();
//        $this->initializeControllersPublishing();

        $this->initializeRoutes();
        $this->initializeRoutesPublishing();

        $this->initializeViews();
        $this->initializeViewsPublishing();
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
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'sidekick');
    }

    /**
     * @return void
     */
    private function initializeRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    /**
     * @return void
     */
    private function initializeRoutesPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../routes/web.php' => base_path('routes/sidekick.php'),
        ], 'routes');
    }

    /**
     * @return void
     */
    private function initializeViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'sidekick');
    }

    /**
     * @return void
     */
    private function initializeViewsPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/sidekick'),
        ], 'views');
    }

    private function initializeControllers()
    {
    }

    private function initializeControllersPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../src/Controllers' => app_path('Http/Controllers/Vendor/Sidekick'),
        ], 'controllers');
    }

}
