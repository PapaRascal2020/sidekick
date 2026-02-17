<?php

namespace PapaRascalDev\Sidekick;

use Illuminate\Support\ServiceProvider;
use PapaRascalDev\Sidekick\Console\IngestCommand;
use PapaRascalDev\Sidekick\Console\InstallCommand;
use PapaRascalDev\Sidekick\Contracts\SearchesKnowledge;

class SidekickServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sidekick.php', 'sidekick');

        $this->app->singleton('sidekick', function ($app) {
            return new SidekickManager($app);
        });

        $this->app->alias('sidekick', SidekickManager::class);

        $this->app->bind(SearchesKnowledge::class, function ($app) {
            $driver = config('sidekick.knowledge.search.driver', Knowledge\VectorSearch::class);

            return new $driver;
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/sidekick.php' => $this->app->configPath('sidekick.php'),
            ], 'sidekick-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'sidekick-migrations');

            $this->commands([
                InstallCommand::class,
                IngestCommand::class,
            ]);
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sidekick');

        $this->publishes([
            __DIR__.'/../resources/views' => $this->app->resourcePath('views/vendor/sidekick'),
        ], 'sidekick-views');

        if (config('sidekick.widget.enabled', false)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/sidekick.php');
        }
    }
}
