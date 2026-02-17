<?php

namespace PapaRascalDev\Sidekick\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use PapaRascalDev\Sidekick\Facades\Sidekick;
use PapaRascalDev\Sidekick\SidekickServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            SidekickServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Sidekick' => Sidekick::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('sidekick.providers.openai.api_key', 'test-openai-key');
        $app['config']->set('sidekick.providers.anthropic.api_key', 'test-anthropic-key');
        $app['config']->set('sidekick.providers.mistral.api_key', 'test-mistral-key');
        $app['config']->set('sidekick.providers.cohere.api_key', 'test-cohere-key');
    }
}
