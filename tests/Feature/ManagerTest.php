<?php

namespace PapaRascalDev\Sidekick\Tests\Feature;

use PapaRascalDev\Sidekick\Builders\AudioBuilder;
use PapaRascalDev\Sidekick\Builders\ConversationBuilder;
use PapaRascalDev\Sidekick\Builders\EmbeddingBuilder;
use PapaRascalDev\Sidekick\Builders\ImageBuilder;
use PapaRascalDev\Sidekick\Builders\ModerationBuilder;
use PapaRascalDev\Sidekick\Builders\TextBuilder;
use PapaRascalDev\Sidekick\Builders\TranscriptionBuilder;
use PapaRascalDev\Sidekick\Exceptions\ConfigurationException;
use PapaRascalDev\Sidekick\Providers\AnthropicProvider;
use PapaRascalDev\Sidekick\Providers\CohereProvider;
use PapaRascalDev\Sidekick\Providers\MistralProvider;
use PapaRascalDev\Sidekick\Providers\OpenAiProvider;
use PapaRascalDev\Sidekick\SidekickManager;
use PapaRascalDev\Sidekick\Tests\TestCase;

class ManagerTest extends TestCase
{
    public function test_manager_is_bound_in_container(): void
    {
        $manager = app('sidekick');

        $this->assertInstanceOf(SidekickManager::class, $manager);
    }

    public function test_manager_resolves_openai_provider(): void
    {
        $manager = app('sidekick');
        $provider = $manager->provider('openai');

        $this->assertInstanceOf(OpenAiProvider::class, $provider);
    }

    public function test_manager_resolves_anthropic_provider(): void
    {
        $manager = app('sidekick');
        $provider = $manager->provider('anthropic');

        $this->assertInstanceOf(AnthropicProvider::class, $provider);
    }

    public function test_manager_resolves_mistral_provider(): void
    {
        $manager = app('sidekick');
        $provider = $manager->provider('mistral');

        $this->assertInstanceOf(MistralProvider::class, $provider);
    }

    public function test_manager_resolves_cohere_provider(): void
    {
        $manager = app('sidekick');
        $provider = $manager->provider('cohere');

        $this->assertInstanceOf(CohereProvider::class, $provider);
    }

    public function test_manager_throws_for_invalid_provider(): void
    {
        $this->expectException(ConfigurationException::class);

        $manager = app('sidekick');
        $manager->provider('nonexistent');
    }

    public function test_manager_returns_text_builder(): void
    {
        $manager = app('sidekick');

        $this->assertInstanceOf(TextBuilder::class, $manager->text());
    }

    public function test_manager_returns_image_builder(): void
    {
        $manager = app('sidekick');

        $this->assertInstanceOf(ImageBuilder::class, $manager->image());
    }

    public function test_manager_returns_audio_builder(): void
    {
        $manager = app('sidekick');

        $this->assertInstanceOf(AudioBuilder::class, $manager->audio());
    }

    public function test_manager_returns_transcription_builder(): void
    {
        $manager = app('sidekick');

        $this->assertInstanceOf(TranscriptionBuilder::class, $manager->transcription());
    }

    public function test_manager_returns_embedding_builder(): void
    {
        $manager = app('sidekick');

        $this->assertInstanceOf(EmbeddingBuilder::class, $manager->embedding());
    }

    public function test_manager_returns_moderation_builder(): void
    {
        $manager = app('sidekick');

        $this->assertInstanceOf(ModerationBuilder::class, $manager->moderation());
    }

    public function test_manager_returns_conversation_builder(): void
    {
        $manager = app('sidekick');

        $this->assertInstanceOf(ConversationBuilder::class, $manager->conversation());
    }

    public function test_manager_caches_provider_instances(): void
    {
        $manager = app('sidekick');

        $provider1 = $manager->provider('openai');
        $provider2 = $manager->provider('openai');

        $this->assertSame($provider1, $provider2);
    }
}
