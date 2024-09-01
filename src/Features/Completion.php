<?php

namespace PapaRascalDev\Sidekick\Features;

use Illuminate\Support\Facades\Http;
use phpDocumentor\Reflection\Types\Boolean;

class Completion
{
    function __construct(
        protected string $url,
        protected array $headers,
        protected bool $inlinePrompt = true,
        protected bool $submitTypes = false,
        protected array $payload = []
    )
    {}

    public function sendMessage(
        string $model,
        string $systemPrompt = "",
        array $messages = [],
        int $maxTokens = 1024
    ): array
    {
        if($this->submitTypes) {
            foreach ($messages as $message) {
                $text = $message['content'];
                $message['content'] = [
                    'type' => 'text',
                    'text' => $text
                ];
            }
        }

        if($this->inlinePrompt) {
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ...$messages
                ],
                'max_tokens' => $maxTokens,
            ];
        } else {
            $payload = [
                'model' => $model,
                'system' => $systemPrompt,
                'messages' => $messages,
                'max_tokens' => $maxTokens
            ];
        }

        return Http::withHeaders($this->headers)
            ->post($this->url, $payload)->json();
    }
}
