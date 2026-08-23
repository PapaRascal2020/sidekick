<?php

namespace PapaRascalDev\Sidekick\Providers;

use Generator;
use Illuminate\Http\Client\PendingRequest;
use PapaRascalDev\Sidekick\Contracts\ProvidesText;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\ValueObjects\Message;
use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\Tool;
use PapaRascalDev\Sidekick\ValueObjects\ToolCall;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

class AnthropicProvider extends AbstractProvider implements ProvidesText
{
    public function name(): string
    {
        return 'anthropic';
    }

    public function capabilities(): array
    {
        return [Capability::Text];
    }

    protected function applyAuth(PendingRequest $request): PendingRequest
    {
        return $request->withHeaders([
            'x-api-key' => $this->config['api_key'],
            'anthropic-version' => $this->config['api_version'] ?? '2023-06-01',
        ]);
    }

    protected function extractStreamedText(array $data): ?string
    {
        if (($data['type'] ?? '') === 'content_block_delta') {
            return $data['delta']['text'] ?? null;
        }

        return null;
    }

    public function generateText(string $model, array $messages, ?string $systemPrompt = null, int $maxTokens = 1024, float $temperature = 1.0, array $tools = []): TextResponse
    {
        $payload = $this->buildPayload($model, $messages, $systemPrompt, $maxTokens, $temperature);

        if ($tools !== []) {
            $payload['tools'] = $this->formatTools($tools);
        }

        $startTime = microtime(true);

        $data = $this->post('/messages', $payload);

        $latency = (microtime(true) - $startTime) * 1000;

        $text = '';
        $toolCalls = [];
        foreach ($data['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text .= $block['text'];
            }

            if (($block['type'] ?? '') === 'tool_use') {
                $toolCalls[] = new ToolCall(
                    name: $block['name'] ?? '',
                    arguments: $block['input'] ?? [],
                    id: $block['id'] ?? null,
                );
            }
        }

        return new TextResponse(
            text: $text,
            usage: Usage::fromArray($data['usage'] ?? []),
            meta: new Meta(
                provider: $this->name(),
                model: $data['model'] ?? $model,
                requestId: $data['id'] ?? null,
                latencyMs: $latency,
            ),
            finishReason: $data['stop_reason'] ?? null,
            toolCalls: $toolCalls,
        );
    }

    public function toolResultMessages(TextResponse $response, array $results): array
    {
        $assistantContent = [];

        if ($response->text !== '') {
            $assistantContent[] = ['type' => 'text', 'text' => $response->text];
        }

        foreach ($response->toolCalls as $call) {
            $assistantContent[] = [
                'type' => 'tool_use',
                'id' => $call->id,
                'name' => $call->name,
                'input' => $call->arguments === [] ? (object) [] : $call->arguments,
            ];
        }

        $toolResults = [];
        foreach ($results as $result) {
            $toolResults[] = [
                'type' => 'tool_result',
                'tool_use_id' => $result['id'],
                'content' => $result['output'],
            ];
        }

        return [
            ['role' => 'assistant', 'content' => $assistantContent],
            ['role' => 'user', 'content' => $toolResults],
        ];
    }

    /**
     * @param  Tool[]  $tools
     * @return array<int, array<string, mixed>>
     */
    private function formatTools(array $tools): array
    {
        return array_map(fn (Tool $tool) => [
            'name' => $tool->name,
            'description' => $tool->description,
            'input_schema' => $tool->schema(),
        ], $tools);
    }

    public function streamText(string $model, array $messages, ?string $systemPrompt = null, int $maxTokens = 1024, float $temperature = 1.0): Generator
    {
        $payload = $this->buildPayload($model, $messages, $systemPrompt, $maxTokens, $temperature);
        $payload['stream'] = true;

        return $this->streamPost('/messages', $payload);
    }

    private function buildPayload(string $model, array $messages, ?string $systemPrompt, int $maxTokens, float $temperature): array
    {
        $formattedMessages = [];

        foreach ($messages as $message) {
            if ($message instanceof Message) {
                $formattedMessages[] = $message->toArray();
            } else {
                $formattedMessages[] = $message;
            }
        }

        $payload = [
            'model' => $model,
            'messages' => $formattedMessages,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ];

        if ($systemPrompt !== null) {
            $payload['system'] = $systemPrompt;
        }

        return $payload;
    }
}
