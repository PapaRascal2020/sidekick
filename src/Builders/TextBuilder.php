<?php

namespace PapaRascalDev\Sidekick\Builders;

use PapaRascalDev\Sidekick\Contracts\ProvidesText;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Enums\Role;
use PapaRascalDev\Sidekick\Events\RequestFailed;
use PapaRascalDev\Sidekick\Events\RequestSending;
use PapaRascalDev\Sidekick\Events\ResponseReceived;
use PapaRascalDev\Sidekick\Events\StreamChunkReceived;
use PapaRascalDev\Sidekick\Exceptions\UnsupportedCapabilityException;
use PapaRascalDev\Sidekick\Responses\StreamResponse;
use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\SidekickManager;
use PapaRascalDev\Sidekick\ValueObjects\Message;
use PapaRascalDev\Sidekick\ValueObjects\Tool;
use PapaRascalDev\Sidekick\ValueObjects\ToolCall;

class TextBuilder
{
    private ?string $provider = null;
    private ?string $model = null;
    private ?string $systemPrompt = null;
    private array $messages = [];
    private int $maxTokens = 1024;
    private float $temperature = 1.0;

    /** @var Tool[] */
    private array $tools = [];

    private int $maxToolCalls = 5;

    public function __construct(
        private readonly SidekickManager $manager,
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

    /**
     * @param  Message[]|array[]  $messages
     */
    public function withMessages(array $messages): self
    {
        $this->messages = array_map(function ($message) {
            if ($message instanceof Message) {
                return $message;
            }

            return Message::fromArray($message);
        }, $messages);

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

    /**
     * @param  array<int, Tool|array>  $tools
     */
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

    /**
     * Maximum number of automatic tool-execution rounds before returning control to the caller.
     */
    public function withMaxToolCalls(int $maxToolCalls): self
    {
        $this->maxToolCalls = $maxToolCalls;

        return $this;
    }

    public function generate(): TextResponse
    {
        $provider = $this->resolveProvider();

        $messages = $this->messages;
        $toolMap = $this->toolMap();
        $steps = 0;

        while (true) {
            event(new RequestSending($this->provider, $this->model, Capability::Text));

            try {
                $response = $provider->generateText(
                    model: $this->model,
                    messages: $messages,
                    systemPrompt: $this->systemPrompt,
                    maxTokens: $this->maxTokens,
                    temperature: $this->temperature,
                    tools: array_values($this->tools),
                );

                event(new ResponseReceived($this->provider, $this->model, Capability::Text, $response));
            } catch (\Throwable $e) {
                event(new RequestFailed($this->provider, $this->model, Capability::Text, $e));

                throw $e;
            }

            // No tools configured, or the model returned a final answer: we are done.
            if ($this->tools === [] || ! $response->hasToolCalls()) {
                return $response;
            }

            // Hand control back to the caller if we hit the ceiling or a tool has no handler.
            if ($steps >= $this->maxToolCalls || ! $this->canHandle($response->toolCalls, $toolMap)) {
                return $response;
            }

            $results = [];
            foreach ($response->toolCalls as $call) {
                $results[] = [
                    'id' => $call->id,
                    'name' => $call->name,
                    'output' => $toolMap[$call->name]->execute($call->arguments),
                ];
            }

            $messages = array_merge($messages, $provider->toolResultMessages($response, $results));
            $steps++;
        }
    }

    /**
     * @return array<string, Tool>
     */
    private function toolMap(): array
    {
        $map = [];

        foreach ($this->tools as $tool) {
            $map[$tool->name] = $tool;
        }

        return $map;
    }

    /**
     * @param  ToolCall[]  $toolCalls
     * @param  array<string, Tool>  $toolMap
     */
    private function canHandle(array $toolCalls, array $toolMap): bool
    {
        foreach ($toolCalls as $call) {
            if (! isset($toolMap[$call->name]) || ! $toolMap[$call->name]->hasHandler()) {
                return false;
            }
        }

        return true;
    }

    public function stream(): StreamResponse
    {
        $provider = $this->resolveProvider();

        event(new RequestSending($this->provider, $this->model, Capability::Text));

        $generator = $provider->streamText(
            model: $this->model,
            messages: $this->messages,
            systemPrompt: $this->systemPrompt,
            maxTokens: $this->maxTokens,
            temperature: $this->temperature,
        );

        $providerName = $this->provider;
        $modelName = $this->model;

        $wrappedGenerator = (function () use ($generator, $providerName, $modelName) {
            foreach ($generator as $chunk) {
                event(new StreamChunkReceived($providerName, $modelName, $chunk));
                yield $chunk;
            }
        })();

        return new StreamResponse($wrappedGenerator);
    }

    private function resolveProvider(): ProvidesText
    {
        [$providerName, $model] = $this->resolveDefaults();
        $this->provider = $providerName;
        $this->model = $model;

        $provider = $this->manager->provider($providerName);

        if (! $provider instanceof ProvidesText) {
            throw new UnsupportedCapabilityException($providerName, Capability::Text);
        }

        return $provider;
    }

    private function resolveDefaults(): array
    {
        $providerName = $this->provider ?? config('sidekick.defaults.text.provider', config('sidekick.default'));
        $model = $this->model ?? config('sidekick.defaults.text.model', 'gpt-4o');

        return [$providerName, $model];
    }
}
