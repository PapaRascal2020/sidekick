<?php

namespace PapaRascalDev\Sidekick\Tests\Unit;

use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Exceptions\ConfigurationException;
use PapaRascalDev\Sidekick\Exceptions\SidekickException;
use PapaRascalDev\Sidekick\Exceptions\UnsupportedCapabilityException;
use PapaRascalDev\Sidekick\Tests\TestCase;

class ExceptionsTest extends TestCase
{
    public function test_configuration_exception_missing_api_key(): void
    {
        $exception = ConfigurationException::missingApiKey('openai');

        $this->assertInstanceOf(SidekickException::class, $exception);
        $this->assertStringContainsString('openai', $exception->getMessage());
    }

    public function test_configuration_exception_invalid_provider(): void
    {
        $exception = ConfigurationException::invalidProvider('unknown');

        $this->assertStringContainsString('unknown', $exception->getMessage());
    }

    public function test_unsupported_capability_exception(): void
    {
        $exception = new UnsupportedCapabilityException('anthropic', Capability::Image);

        $this->assertStringContainsString('anthropic', $exception->getMessage());
        $this->assertStringContainsString('image', $exception->getMessage());
    }
}
