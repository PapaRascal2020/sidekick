<?php

namespace PapaRascalDev\Sidekick\Providers;

use Generator;
use Illuminate\Http\Client\PendingRequest;
use PapaRascalDev\Sidekick\Contracts\ProvidesText;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Exceptions\SidekickException;
use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\ValueObjects\Message;
use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

class CohereProvider extends AbstractProvider implements ProvidesText
{
    public function name(): string
    {
        return 'cohere';
    }

    public function capabilities(): array
    {
        return [Capability::Text];
    }

    protected function applyAuth(PendingRequest $request): PendingRequest
    {
        return $request->withToken($this->config['api_key']);
    }

    protected function extractStreamedText(array $data): ?string
    {
        if (($data['type'] ?? '') === 'content-delta') {
            return $data['delta']['message']['content']['text'] ?? null;
        }

        return null;
    }

    public function generateText(string $model, array $messages, ?string $systemPrompt = null, int $maxTokens = 1024, float $temperature = 1.0, array $tools = []): TextResponse
    {
        if ($tools !== []) {
            throw new SidekickException('Tool calling is not yet supported for the Cohere provider. Use OpenAI, Anthropic, or Mistral for tools.');
        }

        $payload = $this->buildPayload($model, $messages, $systemPrompt, $maxTokens, $temperature);
        $startTime = microtime(true);

        $data = $this->post('/chat', $payload);

        $latency = (microtime(true) - $startTime) * 1000;

        $text = '';
        foreach ($data['message']['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text .= $block['text'];
            }
        }

        return new TextResponse(
            text: $text,
            usage: Usage::fromArray([
                'prompt_tokens' => $data['usage']['tokens']['input_tokens'] ?? 0,
                'completion_tokens' => $data['usage']['tokens']['output_tokens'] ?? 0,
            ]),
            meta: new Meta(
                provider: $this->name(),
                model: $data['model'] ?? $model,
                requestId: $data['id'] ?? null,
                latencyMs: $latency,
            ),
            finishReason: $data['finish_reason'] ?? null,
        );
    }

    public function streamText(string $model, array $messages, ?string $systemPrompt = null, int $maxTokens = 1024, float $temperature = 1.0): Generator
    {
        $payload = $this->buildPayload($model, $messages, $systemPrompt, $maxTokens, $temperature);
        $payload['stream'] = true;

        return $this->streamPost('/chat', $payload);
    }

    public function toolResultMessages(TextResponse $response, array $results): array
    {
        throw new SidekickException('Tool calling is not yet supported for the Cohere provider.');
    }

    private function buildPayload(string $model, array $messages, ?string $systemPrompt, int $maxTokens, float $temperature): array
    {
        $formattedMessages = [];

        if ($systemPrompt !== null) {
            $formattedMessages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        foreach ($messages as $message) {
            if ($message instanceof Message) {
                $formattedMessages[] = [
                    'role' => $this->mapRole($message->role->value),
                    'content' => $message->content,
                ];
            } else {
                $formattedMessages[] = [
                    'role' => $this->mapRole($message['role'] ?? 'user'),
                    'content' => $message['content'] ?? '',
                ];
            }
        }

        return [
            'model' => $model,
            'messages' => $formattedMessages,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ];
    }

    private function mapRole(string $role): string
    {
        return match ($role) {
            'assistant' => 'assistant',
            'system' => 'system',
            default => 'user',
        };
    }
}
