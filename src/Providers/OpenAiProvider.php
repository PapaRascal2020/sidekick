<?php

namespace PapaRascalDev\Sidekick\Providers;

use Generator;
use Illuminate\Http\Client\PendingRequest;
use PapaRascalDev\Sidekick\Contracts\ProvidesAudio;
use PapaRascalDev\Sidekick\Contracts\ProvidesEmbeddings;
use PapaRascalDev\Sidekick\Contracts\ProvidesImages;
use PapaRascalDev\Sidekick\Contracts\ProvidesModeration;
use PapaRascalDev\Sidekick\Contracts\ProvidesText;
use PapaRascalDev\Sidekick\Contracts\ProvidesTranscription;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Responses\AudioResponse;
use PapaRascalDev\Sidekick\Responses\EmbeddingResponse;
use PapaRascalDev\Sidekick\Responses\ImageResponse;
use PapaRascalDev\Sidekick\Responses\ModerationResponse;
use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\Responses\TranscriptionResponse;
use PapaRascalDev\Sidekick\ValueObjects\Message;
use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\ModerationCategory;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

class OpenAiProvider extends AbstractProvider implements ProvidesText, ProvidesImages, ProvidesAudio, ProvidesTranscription, ProvidesEmbeddings, ProvidesModeration
{
    public function name(): string
    {
        return 'openai';
    }

    public function capabilities(): array
    {
        return [
            Capability::Text,
            Capability::Image,
            Capability::Audio,
            Capability::Transcription,
            Capability::Embedding,
            Capability::Moderation,
        ];
    }

    protected function applyAuth(PendingRequest $request): PendingRequest
    {
        return $request->withToken($this->config['api_key']);
    }

    protected function extractStreamedText(array $data): ?string
    {
        return $data['choices'][0]['delta']['content'] ?? null;
    }

    public function generateText(string $model, array $messages, ?string $systemPrompt = null, int $maxTokens = 1024, float $temperature = 1.0): TextResponse
    {
        $payload = $this->buildChatPayload($model, $messages, $systemPrompt, $maxTokens, $temperature);
        $startTime = microtime(true);

        $data = $this->post('/chat/completions', $payload);

        $latency = (microtime(true) - $startTime) * 1000;

        return new TextResponse(
            text: $data['choices'][0]['message']['content'] ?? '',
            usage: Usage::fromArray($data['usage'] ?? []),
            meta: new Meta(
                provider: $this->name(),
                model: $data['model'] ?? $model,
                requestId: $data['id'] ?? null,
                latencyMs: $latency,
            ),
            finishReason: $data['choices'][0]['finish_reason'] ?? null,
        );
    }

    public function streamText(string $model, array $messages, ?string $systemPrompt = null, int $maxTokens = 1024, float $temperature = 1.0): Generator
    {
        $payload = $this->buildChatPayload($model, $messages, $systemPrompt, $maxTokens, $temperature);
        $payload['stream'] = true;

        return $this->streamPost('/chat/completions', $payload);
    }

    public function generateImage(string $model, string $prompt, string $size = '1024x1024', string $quality = 'standard', int $count = 1): ImageResponse
    {
        $startTime = microtime(true);

        $data = $this->post('/images/generations', [
            'model' => $model,
            'prompt' => $prompt,
            'n' => $count,
            'size' => $size,
            'quality' => $quality,
        ]);

        $latency = (microtime(true) - $startTime) * 1000;

        $urls = array_map(fn (array $item) => $item['url'], $data['data'] ?? []);
        $revisedPrompt = $data['data'][0]['revised_prompt'] ?? null;

        return new ImageResponse(
            urls: $urls,
            meta: new Meta(provider: $this->name(), model: $model, latencyMs: $latency),
            revisedPrompt: $revisedPrompt,
        );
    }

    public function generateAudio(string $model, string $text, string $voice = 'alloy', string $format = 'mp3'): AudioResponse
    {
        $startTime = microtime(true);

        $content = $this->postRaw('/audio/speech', [
            'model' => $model,
            'input' => $text,
            'voice' => $voice,
            'response_format' => $format,
        ]);

        $latency = (microtime(true) - $startTime) * 1000;

        return new AudioResponse(
            content: $content,
            format: $format,
            meta: new Meta(provider: $this->name(), model: $model, latencyMs: $latency),
        );
    }

    public function transcribe(string $model, string $filePath, ?string $language = null): TranscriptionResponse
    {
        $startTime = microtime(true);

        $multipart = [
            ['name' => 'model', 'contents' => $model],
            ['name' => 'file', 'contents' => fopen($filePath, 'r'), 'filename' => basename($filePath)],
        ];

        if ($language !== null) {
            $multipart[] = ['name' => 'language', 'contents' => $language];
        }

        $data = $this->postMultipart('/audio/transcriptions', $multipart);

        $latency = (microtime(true) - $startTime) * 1000;

        return new TranscriptionResponse(
            text: $data['text'] ?? '',
            meta: new Meta(provider: $this->name(), model: $model, latencyMs: $latency),
            language: $data['language'] ?? $language,
            duration: $data['duration'] ?? null,
        );
    }

    public function generateEmbedding(string $model, string|array $input): EmbeddingResponse
    {
        $startTime = microtime(true);

        $data = $this->post('/embeddings', [
            'model' => $model,
            'input' => $input,
        ]);

        $latency = (microtime(true) - $startTime) * 1000;

        $embeddings = array_map(fn (array $item) => $item['embedding'], $data['data'] ?? []);

        return new EmbeddingResponse(
            embeddings: $embeddings,
            usage: Usage::fromArray($data['usage'] ?? []),
            meta: new Meta(provider: $this->name(), model: $model, latencyMs: $latency),
        );
    }

    public function moderate(string $model, string $content): ModerationResponse
    {
        $startTime = microtime(true);

        $data = $this->post('/moderations', [
            'model' => $model,
            'input' => $content,
        ]);

        $latency = (microtime(true) - $startTime) * 1000;

        $result = $data['results'][0] ?? [];
        $categories = [];

        foreach ($result['categories'] ?? [] as $category => $flagged) {
            $score = $result['category_scores'][$category] ?? null;
            $categories[] = new ModerationCategory(
                category: $category,
                flagged: $flagged,
                score: $score,
            );
        }

        return new ModerationResponse(
            flagged: $result['flagged'] ?? false,
            categories: $categories,
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
