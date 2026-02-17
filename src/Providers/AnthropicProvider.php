<?php

namespace PapaRascalDev\Sidekick\Providers;

use Generator;
use Illuminate\Http\Client\PendingRequest;
use PapaRascalDev\Sidekick\Contracts\ProvidesText;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\ValueObjects\Message;
use PapaRascalDev\Sidekick\ValueObjects\Meta;
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

    public function generateText(string $model, array $messages, ?string $systemPrompt = null, int $maxTokens = 1024, float $temperature = 1.0): TextResponse
    {
        $payload = $this->buildPayload($model, $messages, $systemPrompt, $maxTokens, $temperature);
        $startTime = microtime(true);

        $data = $this->post('/messages', $payload);

        $latency = (microtime(true) - $startTime) * 1000;

        $text = '';
        foreach ($data['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text .= $block['text'];
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
        );
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
