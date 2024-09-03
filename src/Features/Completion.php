<?php

namespace PapaRascalDev\Sidekick\Features;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class Completion
{
    /**
     * @param string $url
     * @param array $headers
     * @param bool $inlinePrompt
     * @param bool $submitTypes
     * @param array $payload
     */
    function __construct(
        protected string $url,
        protected array $headers,
        protected bool $inlinePrompt = true,
        protected bool $submitTypes = false,
        protected array $payload = []
    )
    {}

    /**
     * Send Message
     *
     * Sends a message to the given model and returns the response.
     *
     * @param string $model
     * @param string $systemPrompt
     * @param array $messages
     * @param int $maxTokens
     * @return array
     * @throws ConnectionException
     */
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
