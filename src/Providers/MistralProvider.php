<?php

namespace PapaRascalDev\Sidekick\Providers;

use Generator;
use Illuminate\Http\Client\PendingRequest;
use PapaRascalDev\Sidekick\Contracts\ProvidesEmbeddings;
use PapaRascalDev\Sidekick\Contracts\ProvidesText;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Providers\Concerns\FormatsJsonSchema;
use PapaRascalDev\Sidekick\Providers\Concerns\HandlesOpenAiTools;
use PapaRascalDev\Sidekick\Responses\EmbeddingResponse;
use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\ValueObjects\Message;
use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\Schema;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

class MistralProvider extends AbstractProvider implements ProvidesText, ProvidesEmbeddings
{
    use FormatsJsonSchema;
    use HandlesOpenAiTools;

    public function name(): string
    {
        return 'mistral';
    }

    public function capabilities(): array
    {
        return [Capability::Text, Capability::Embedding];
    }

    protected function applyAuth(PendingRequest $request): PendingRequest
    {
        return $request->withToken($this->config['api_key']);
    }

    protected function extractStreamedText(array $data): ?string
    {
        return $data['choices'][0]['delta']['content'] ?? null;
    }

    public function generateText(string $model, array $messages, ?string $systemPrompt = null, int $maxTokens = 1024, float $temperature = 1.0, array $tools = [], ?Schema $schema = null): TextResponse
    {
        $payload = $this->buildChatPayload($model, $messages, $systemPrompt, $maxTokens, $temperature);

        if ($tools !== []) {
            $payload['tools'] = $this->formatTools($tools);
        }

        if ($schema !== null) {
            $payload['response_format'] = $this->jsonSchemaResponseFormat($schema);
        }

        $startTime = microtime(true);

        $data = $this->post('/chat/completions', $payload);

        $latency = (microtime(true) - $startTime) * 1000;

        $content = $data['choices'][0]['message']['content'] ?? '';

        return new TextResponse(
            text: $content,
            usage: Usage::fromArray($data['usage'] ?? []),
            meta: new Meta(
                provider: $this->name(),
                model: $data['model'] ?? $model,
                requestId: $data['id'] ?? null,
                latencyMs: $latency,
            ),
            finishReason: $data['choices'][0]['finish_reason'] ?? null,
            toolCalls: $this->parseToolCalls($data),
            structured: $schema !== null ? $this->decodeStructured($content) : null,
        );
    }

    public function toolResultMessages(TextResponse $response, array $results): array
    {
        return $this->openAiToolResultMessages($response, $results, includeName: true);
    }

    public function streamText(string $model, array $messages, ?string $systemPrompt = null, int $maxTokens = 1024, float $temperature = 1.0): Generator
    {
        $payload = $this->buildChatPayload($model, $messages, $systemPrompt, $maxTokens, $temperature);
        $payload['stream'] = true;

        return $this->streamPost('/chat/completions', $payload);
    }

    public function generateEmbedding(string $model, string|array $input): EmbeddingResponse
    {
        $startTime = microtime(true);

        $data = $this->post('/embeddings', [
            'model' => $model,
            'input' => is_array($input) ? $input : [$input],
        ]);

        $latency = (microtime(true) - $startTime) * 1000;

        $embeddings = array_map(fn (array $item) => $item['embedding'], $data['data'] ?? []);

        return new EmbeddingResponse(
            embeddings: $embeddings,
            usage: Usage::fromArray($data['usage'] ?? []),
            meta: new Meta(provider: $this->name(), model: $model, latencyMs: $latency),
        );
    }

    private function buildChatPayload(string $model, array $messages, ?string $systemPrompt, int $maxTokens, float $temperature): array
    {
        $formattedMessages = [];

        if ($systemPrompt !== null) {
            $formattedMessages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        foreach ($messages as $message) {
            if ($message instanceof Message) {
                $formattedMessages[] = $message->toArray();
            } else {
                $formattedMessages[] = $message;
            }
        }

        return [
            'model' => $model,
            'messages' => $formattedMessages,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ];
    }
}
