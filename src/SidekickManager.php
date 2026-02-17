<?php

namespace PapaRascalDev\Sidekick;

use Closure;
use Illuminate\Support\Manager;
use PapaRascalDev\Sidekick\Builders\AudioBuilder;
use PapaRascalDev\Sidekick\Builders\ConversationBuilder;
use PapaRascalDev\Sidekick\Builders\EmbeddingBuilder;
use PapaRascalDev\Sidekick\Builders\ImageBuilder;
use PapaRascalDev\Sidekick\Builders\KnowledgeBuilder;
use PapaRascalDev\Sidekick\Builders\ModerationBuilder;
use PapaRascalDev\Sidekick\Builders\TextBuilder;
use PapaRascalDev\Sidekick\Builders\TranscriptionBuilder;
use PapaRascalDev\Sidekick\Contracts\ProviderContract;
use PapaRascalDev\Sidekick\Exceptions\ConfigurationException;
use PapaRascalDev\Sidekick\Providers\AnthropicProvider;
use PapaRascalDev\Sidekick\Providers\CohereProvider;
use PapaRascalDev\Sidekick\Providers\MistralProvider;
use PapaRascalDev\Sidekick\Providers\OpenAiProvider;
use PapaRascalDev\Sidekick\Testing\SidekickFake;

class SidekickManager extends Manager
{
    protected array $providers = [];
    protected array $customProviderResolvers = [];

    public function getDefaultDriver(): string
    {
        return $this->config->get('sidekick.default', 'openai');
    }

    public function provider(string $name): ProviderContract
    {
        if (isset($this->providers[$name])) {
            return $this->providers[$name];
        }

        return $this->providers[$name] = $this->resolveProvider($name);
    }

    protected function resolveProvider(string $name): ProviderContract
    {
        // Check custom providers registered via registerProvider()
        if (isset($this->customProviderResolvers[$name])) {
            $resolver = $this->customProviderResolvers[$name];

            return $resolver($this->container);
        }

        // Check config-based custom providers
        $customProviders = $this->config->get('sidekick.custom_providers', []);
        if (isset($customProviders[$name])) {
            $class = $customProviders[$name];

            return $this->container->make($class, [
                'config' => $this->config->get("sidekick.providers.{$name}", []),
            ]);
        }

        // Built-in providers
        $config = $this->config->get("sidekick.providers.{$name}");

        if ($config === null) {
            throw ConfigurationException::invalidProvider($name);
        }

        return match ($name) {
            'openai' => new OpenAiProvider($config),
            'anthropic' => new AnthropicProvider($config),
            'mistral' => new MistralProvider($config),
            'cohere' => new CohereProvider($config),
            default => throw ConfigurationException::invalidProvider($name),
        };
    }

    // ----- Builder Entry Points -----

    public function text(): TextBuilder
    {
        return new TextBuilder($this);
    }

    public function image(): ImageBuilder
    {
        return new ImageBuilder($this);
    }

    public function audio(): AudioBuilder
    {
        return new AudioBuilder($this);
    }

    public function transcription(): TranscriptionBuilder
    {
        return new TranscriptionBuilder($this);
    }

    public function embedding(): EmbeddingBuilder
    {
        return new EmbeddingBuilder($this);
    }

    public function moderation(): ModerationBuilder
    {
        return new ModerationBuilder($this);
    }

    public function conversation(): ConversationBuilder
    {
        return new ConversationBuilder($this);
    }

    public function knowledge(string $name): KnowledgeBuilder
    {
        return (new KnowledgeBuilder($this))->for($name);
    }

    // ----- Utility Methods -----

    public function summarize(string $content, int $maxLength = 500): string
    {
        $response = $this->text()
            ->withSystemPrompt("Summarize the following text concisely in under {$maxLength} characters.")
            ->withPrompt($content)
            ->generate();

        return $response->text;
    }

    public function translate(string $text, string $targetLanguage): string
    {
        $response = $this->text()
            ->withSystemPrompt("Translate the following text to {$targetLanguage}. Only return the translation, nothing else.")
            ->withPrompt($text)
            ->generate();

        return $response->text;
    }

    public function extractKeywords(string $text): string
    {
        $response = $this->text()
            ->withSystemPrompt('Extract the most important keywords from the following text. Return them as a comma-separated list.')
            ->withPrompt($text)
            ->generate();

        return $response->text;
    }

    // ----- Extension System -----

    public function registerProvider(string $name, Closure|callable $resolver): self
    {
        $this->customProviderResolvers[$name] = $resolver;

        return $this;
    }

    // ----- Testing -----

    public function fake(array $responses = []): SidekickFake
    {
        $fake = new SidekickFake($responses);

        app()->instance('sidekick', $fake);

        return $fake;
    }

    public function createOpenaiDriver(): OpenAiProvider
    {
        return new OpenAiProvider($this->config->get('sidekick.providers.openai', []));
    }

    public function createAnthropicDriver(): AnthropicProvider
    {
        return new AnthropicProvider($this->config->get('sidekick.providers.anthropic', []));
    }

    public function createMistralDriver(): MistralProvider
    {
        return new MistralProvider($this->config->get('sidekick.providers.mistral', []));
    }

    public function createCohereDriver(): CohereProvider
    {
        return new CohereProvider($this->config->get('sidekick.providers.cohere', []));
    }
}
