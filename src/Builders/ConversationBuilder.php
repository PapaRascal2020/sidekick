<?php

namespace PapaRascalDev\Sidekick\Builders;

use PapaRascalDev\Sidekick\Contracts\ProvidesText;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Enums\Role;
use PapaRascalDev\Sidekick\Exceptions\SidekickException;
use PapaRascalDev\Sidekick\Exceptions\UnsupportedCapabilityException;
use PapaRascalDev\Sidekick\Models\Conversation;
use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\SidekickManager;
use PapaRascalDev\Sidekick\ValueObjects\Message;

class ConversationBuilder
{
    private ?string $provider = null;
    private ?string $model = null;
    private ?string $systemPrompt = null;
    private int $maxTokens = 1024;
    private ?Conversation $conversation = null;

    public function __construct(
        private readonly SidekickManager $manager,
    ) {}

    public function using(string $provider, ?string $model = null): self
    {
        $this->provider = $provider;
        $this->model = $model;

        return $this;
    }

    public function withSystemPrompt(string $systemPrompt): self
    {
        $this->systemPrompt = $systemPrompt;

        return $this;
    }

    public function withMaxTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;

        return $this;
    }

    public function begin(): self
    {
        $providerName = $this->provider ?? config('sidekick.defaults.text.provider', config('sidekick.default'));
        $model = $this->model ?? config('sidekick.defaults.text.model', 'gpt-4o');

        $this->provider = $providerName;
        $this->model = $model;

        $this->conversation = Conversation::create([
            'provider' => $providerName,
            'model' => $model,
            'system_prompt' => $this->systemPrompt,
            'max_tokens' => $this->maxTokens,
        ]);

        return $this;
    }

    public function resume(string $conversationId): self
    {
        $this->conversation = Conversation::findOrFail($conversationId);
        $this->provider = $this->conversation->provider;
        $this->model = $this->conversation->model;
        $this->systemPrompt = $this->conversation->system_prompt;
        $this->maxTokens = $this->conversation->max_tokens;

        return $this;
    }

    public function send(string $message): TextResponse
    {
        if ($this->conversation === null) {
            throw new SidekickException('No conversation started. Call begin() or resume() first.');
        }

        $provider = $this->manager->provider($this->provider);

        if (! $provider instanceof ProvidesText) {
            throw new UnsupportedCapabilityException($this->provider, Capability::Text);
        }

        // Store user message
        $this->conversation->messages()->create([
            'role' => Role::User->value,
            'content' => $message,
        ]);

        // Build message history
        $messages = $this->conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn ($msg) => new Message(Role::from($msg->role), $msg->content))
            ->all();

        $response = $provider->generateText(
            model: $this->model,
            messages: $messages,
            systemPrompt: $this->systemPrompt,
            maxTokens: $this->maxTokens,
        );

        // Store assistant response
        $this->conversation->messages()->create([
            'role' => Role::Assistant->value,
            'content' => $response->text,
        ]);

        return $response;
    }

    public function delete(): bool
    {
        if ($this->conversation === null) {
            throw new SidekickException('No conversation loaded. Call begin() or resume() first.');
        }

        return $this->conversation->delete();
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }
}
