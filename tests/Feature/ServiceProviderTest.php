<?php

namespace PapaRascalDev\Sidekick\Tests\Feature;

use PapaRascalDev\Sidekick\SidekickManager;
use PapaRascalDev\Sidekick\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_config_is_merged(): void
    {
        $this->assertNotNull(config('sidekick'));
        $this->assertNotNull(config('sidekick.default'));
        $this->assertNotNull(config('sidekick.providers'));
    }

    public function test_sidekick_is_bound_as_singleton(): void
    {
        $instance1 = app('sidekick');
        $instance2 = app('sidekick');

        $this->assertSame($instance1, $instance2);
        $this->assertInstanceOf(SidekickManager::class, $instance1);
    }

    public function test_sidekick_helper_function_works(): void
    {
        $manager = sidekick();

        $this->assertInstanceOf(SidekickManager::class, $manager);
    }

    public function test_config_has_default_provider(): void
    {
        $this->assertEquals('openai', config('sidekick.default'));
    }

    public function test_config_has_all_providers(): void
    {
        $this->assertArrayHasKey('openai', config('sidekick.providers'));
        $this->assertArrayHasKey('anthropic', config('sidekick.providers'));
        $this->assertArrayHasKey('mistral', config('sidekick.providers'));
        $this->assertArrayHasKey('cohere', config('sidekick.providers'));
    }
}
