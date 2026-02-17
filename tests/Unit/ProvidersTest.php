<?php

namespace PapaRascalDev\Sidekick\Tests\Unit;

use PapaRascalDev\Sidekick\Contracts\ProvidesAudio;
use PapaRascalDev\Sidekick\Contracts\ProvidesEmbeddings;
use PapaRascalDev\Sidekick\Contracts\ProvidesImages;
use PapaRascalDev\Sidekick\Contracts\ProvidesModeration;
use PapaRascalDev\Sidekick\Contracts\ProvidesText;
use PapaRascalDev\Sidekick\Contracts\ProvidesTranscription;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Exceptions\ConfigurationException;
use PapaRascalDev\Sidekick\Providers\AnthropicProvider;
use PapaRascalDev\Sidekick\Providers\CohereProvider;
use PapaRascalDev\Sidekick\Providers\MistralProvider;
use PapaRascalDev\Sidekick\Providers\OpenAiProvider;
use PapaRascalDev\Sidekick\Tests\TestCase;

class ProvidersTest extends TestCase
{
    public function test_openai_provider_supports_all_capabilities(): void
    {
        $provider = new OpenAiProvider(['api_key' => 'test', 'base_url' => 'https://api.openai.com/v1']);

        $this->assertEquals('openai', $provider->name());
        $this->assertTrue($provider->supports(Capability::Text));
        $this->assertTrue($provider->supports(Capability::Image));
        $this->assertTrue($provider->supports(Capability::Audio));
        $this->assertTrue($provider->supports(Capability::Transcription));
        $this->assertTrue($provider->supports(Capability::Embedding));
        $this->assertTrue($provider->supports(Capability::Moderation));

        $this->assertInstanceOf(ProvidesText::class, $provider);
        $this->assertInstanceOf(ProvidesImages::class, $provider);
        $this->assertInstanceOf(ProvidesAudio::class, $provider);
        $this->assertInstanceOf(ProvidesTranscription::class, $provider);
        $this->assertInstanceOf(ProvidesEmbeddings::class, $provider);
        $this->assertInstanceOf(ProvidesModeration::class, $provider);
    }

    public function test_anthropic_provider_supports_text_only(): void
    {
        $provider = new AnthropicProvider(['api_key' => 'test', 'base_url' => 'https://api.anthropic.com/v1']);

        $this->assertEquals('anthropic', $provider->name());
        $this->assertTrue($provider->supports(Capability::Text));
        $this->assertFalse($provider->supports(Capability::Image));
        $this->assertFalse($provider->supports(Capability::Audio));

        $this->assertInstanceOf(ProvidesText::class, $provider);
    }

    public function test_mistral_provider_supports_text_and_embeddings(): void
    {
        $provider = new MistralProvider(['api_key' => 'test', 'base_url' => 'https://api.mistral.ai/v1']);

        $this->assertEquals('mistral', $provider->name());
        $this->assertTrue($provider->supports(Capability::Text));
        $this->assertTrue($provider->supports(Capability::Embedding));
        $this->assertFalse($provider->supports(Capability::Image));

        $this->assertInstanceOf(ProvidesText::class, $provider);
        $this->assertInstanceOf(ProvidesEmbeddings::class, $provider);
    }

    public function test_cohere_provider_supports_text_only(): void
    {
        $provider = new CohereProvider(['api_key' => 'test', 'base_url' => 'https://api.cohere.com/v2']);

        $this->assertEquals('cohere', $provider->name());
        $this->assertTrue($provider->supports(Capability::Text));
        $this->assertFalse($provider->supports(Capability::Image));

        $this->assertInstanceOf(ProvidesText::class, $provider);
    }

    public function test_provider_throws_on_missing_api_key(): void
    {
        $this->expectException(ConfigurationException::class);

        new OpenAiProvider(['api_key' => null, 'base_url' => 'https://api.openai.com/v1']);
    }

    public function test_provider_throws_on_empty_api_key(): void
    {
        $this->expectException(ConfigurationException::class);

        new OpenAiProvider(['api_key' => '', 'base_url' => 'https://api.openai.com/v1']);
    }
}
