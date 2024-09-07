<?php

namespace PapaRascalDev\Sidekick\Drivers;

use PapaRascalDev\Sidekick\Features\{Completion, Audio, Transcribe, Image, Embedding, Moderate};
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;

class OpenAi implements Driver
{

    /**
     * OpenAi Api Base URL
     * @strind $baseUrl
     */
    private string $baseUrl = "https://api.openai.com/v1";

    /**
     * Headers
     *
     * To be passed with every request
     *
     * @array $headers
     */
    protected array $headers;

    public array $messageRoles = [
        'user' => 'user',
        'assistant' => 'assistant'
    ];

    public bool $listAsObject = false;

    public array $chatMaps = [];

    function __construct()
    {
        $apiToken = getenv("SIDEKICK_OPENAI_TOKEN");

        $this->headers = [
            "Authorization" => "Bearer {$apiToken}",
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];
    }

    public function image(): Image
    {
        return new Image(
            url: "{$this->baseUrl}/images/generations",
            headers: $this->headers
        );
    }

    public function audio(): Audio
    {
        return new Audio(
            url: "{$this->baseUrl}/audio/speech",
            headers: $this->headers
        );
    }

    public function transcribe(): Transcribe
    {
        return new Transcribe(
            url: "{$this->baseUrl}/audio/transcriptions",
            headers: $this->headers
        );
    }

    public function complete(): Completion
    {
        return new Completion(
            url: "{$this->baseUrl}/chat/completions",
            headers: $this->headers,
            requestRules: [
                'model' => '$model',
                'max_tokens' => '$maxTokens',
                'messages' => [
                    '$systemPrompt ? ["role" => "system", "content" => $systemPrompt] : null',
                    '$allMessages ? $allMessages : null',
                    '["role" => "user", "content" => $message]',
                    ]
                ],
            responseFormat: []
        );
    }

    public function embedding(): Embedding
    {
        return new Embedding(
            url: "{$this->baseUrl}/embeddings",
            headers: $this->headers
        );
    }

    public function moderate(): Moderate
    {
        return new Moderate(
            url: "{$this->baseUrl}/moderations",
            headers: $this->headers
        );
    }

    public function getResponse($response)
    {
        return $response['choices'][0]['message']['content'];
    }

    public function getErrorMessage($response)
    {
        return [
            'driver' => 'OpenAi',
            'error' => [
                'type' => $response['error']['type'],
                'code' => $response['error']['code'],
                'message' => $response['error']['message']
            ]
        ];
    }

    public function validate($response): bool
    {
        return !isset($response['error']);
    }
}
