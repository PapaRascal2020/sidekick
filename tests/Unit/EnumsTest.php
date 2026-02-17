<?php

namespace PapaRascalDev\Sidekick\Tests\Unit;

use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Enums\Provider;
use PapaRascalDev\Sidekick\Enums\Role;
use PapaRascalDev\Sidekick\Tests\TestCase;

class EnumsTest extends TestCase
{
    public function test_provider_enum_has_all_providers(): void
    {
        $this->assertEquals('openai', Provider::OpenAI->value);
        $this->assertEquals('anthropic', Provider::Anthropic->value);
        $this->assertEquals('mistral', Provider::Mistral->value);
        $this->assertEquals('cohere', Provider::Cohere->value);
    }

    public function test_capability_enum_has_all_capabilities(): void
    {
        $this->assertEquals('text', Capability::Text->value);
        $this->assertEquals('image', Capability::Image->value);
        $this->assertEquals('audio', Capability::Audio->value);
        $this->assertEquals('transcription', Capability::Transcription->value);
        $this->assertEquals('embedding', Capability::Embedding->value);
        $this->assertEquals('moderation', Capability::Moderation->value);
    }

    public function test_role_enum_has_all_roles(): void
    {
        $this->assertEquals('system', Role::System->value);
        $this->assertEquals('user', Role::User->value);
        $this->assertEquals('assistant', Role::Assistant->value);
    }
}
