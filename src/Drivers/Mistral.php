<?php

namespace PapaRascalDev\Sidekick\Drivers;

use PapaRascalDev\Sidekick\Features\{Completion, Embedding};

class Mistral implements Driver
{

    /**
     * OpenAi Api Base URL
     * @strind $baseUrl
     */
    private string $baseUrl = "https://api.mistral.ai/v1";

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
        $apiToken = getenv("SIDEKICK_MISTRAL_TOKEN");

        $this->headers = [
            "Authorization" => "Bearer {$apiToken}",
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];
    }

    /**
     * @return Completion
     */
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

    /**
     * @return Embedding
     */
    public function embedding(): Embedding
    {
        return new Embedding(
            url: "{$this->baseUrl}/embeddings",
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
            'driver' => 'Mistral',
            'error' => [
                'type' => $response['type'],
                'code' => $response['code'],
                'message' => $response['message']
            ]
        ];
    }

    public function validate($response): bool
    {
        return !($response['object'] == "error");
    }
}
