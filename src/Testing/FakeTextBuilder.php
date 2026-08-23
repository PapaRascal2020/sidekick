<?php

namespace PapaRascalDev\Sidekick\Testing;

use PapaRascalDev\Sidekick\Enums\Role;
use PapaRascalDev\Sidekick\Responses\StreamResponse;
use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\ValueObjects\Message;
use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\Schema;
use PapaRascalDev\Sidekick\ValueObjects\Tool;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

class FakeTextBuilder
{
    private ?string $provider = null;
    private ?string $model = null;
    private ?string $systemPrompt = null;
    private array $messages = [];
    private int $maxTokens = 1024;
    private float $temperature = 1.0;
    private array $tools = [];
    private int $maxToolCalls = 5;
    private ?Schema $schema = null;

    public function __construct(
        private readonly SidekickFake $fake,
    ) {}

    public function using(string $provider, ?string $model = null): self
    {
        $this->provider = $provider;
        $this->model = $model;

        return $this;
    }

    public function withPrompt(string $prompt): self
    {
        $this->messages[] = new Message(Role::User, $prompt);

        return $this;
    }

    public function withSystemPrompt(string $systemPrompt): self
    {
        $this->systemPrompt = $systemPrompt;

        return $this;
    }

    public function withMessages(array $messages): self
    {
        $this->messages = $messages;

        return $this;
    }

    public function addMessage(Role $role, string $content): self
    {
        $this->messages[] = new Message($role, $content);

        return $this;
    }

    public function withMaxTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;

        return $this;
    }

    public function withTemperature(float $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function withTools(array $tools): self
    {
        foreach ($tools as $tool) {
            $this->tools[] = $tool instanceof Tool
                ? $tool
                : Tool::make(
                    name: $tool['name'],
                    description: $tool['description'] ?? '',
                    parameters: $tool['parameters'] ?? [],
                );
        }

        return $this;
    }

    public function withTool(Tool $tool): self
    {
        $this->tools[] = $tool;

        return $this;
    }

    public function withMaxToolCalls(int $maxToolCalls): self
    {
        $this->maxToolCalls = $maxToolCalls;

        return $this;
    }

    public function withSchema(array|Schema $schema, string $name = 'response', bool $strict = true): self
    {
        $this->schema = $schema instanceof Schema
            ? $schema
            : new Schema($schema, $name, $strict);

        return $this;
    }

    public function generate(): TextResponse
    {
        $lastMessage = end($this->messages);
        $prompt = $lastMessage instanceof Message ? $lastMessage->content : '';

        $this->fake->record([
            'type' => 'text',
            'provider' => $this->provider,
            'model' => $this->model,
            'prompt' => $prompt,
            'system_prompt' => $this->systemPrompt,
            'max_tokens' => $this->maxTokens,
            'tools' => array_map(fn (Tool $tool) => $tool->name, $this->tools),
        ]);

        $response = $this->fake->nextResponse();

        if ($response instanceof TextResponse) {
            return $response;
        }

        return new TextResponse(
            text: is_string($response) ? $response : 'fake response',
            usage: new Usage(10, 20, 30),
            meta: new Meta(
                provider: $this->provider ?? 'fake',
                model: $this->model ?? 'fake-model',
            ),
            finishReason: 'stop',
        );
    }

    public function stream(): StreamResponse
    {
        $response = $this->generate();

        $text = $response->text;
        $generator = (function () use ($text) {
            yield $text;
        })();

        return new StreamResponse($generator);
    }
}
