<?php

namespace PapaRascalDev\Sidekick\Drivers;

use PapaRascalDev\Sidekick\Features\{Completion, Embedding, StreamedCompletion};
use PapaRascalDev\Sidekick\Utilities\MistralHelper;
use PapaRascalDev\Sidekick\Utilities\Utilities;

/**
 * Supported Models:
 *
 * - claude-3-opus-20240229
 * - claude-3-sonnet-20240229
 * - claude-3-haiku-20240307
 *
 * Supported Methods
 * - Completions
 * - Embed
 */

class Mistral implements Driver
{

    /**
     * Api Base URL
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

    /**
     * Message Roles
     *
     * Some AI tools have different naming for
     * user and bot roles so added this so it
     * can be specified.
     *
     * @array $messageRoles
     */
    public array $messageRoles = [
        'user' => 'user',
        'assistant' => 'assistant'
    ];

    /**
     * @var string
     */
    public string $defaultCompleteModel = "open-mistral-7b";

    /**
     * List As Object
     *
     * This is to specify if the chat history
     * should be sent as an Object or Array
     * to the payload.
     *
     * @array $listAsObject
     */
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
            ]
        );
    }

    public function completeStreamed(): StreamedCompletion
    {
        return new StreamedCompletion (
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
            ]
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

    /**
     * @return Utilities
     */
    public function utilities(): Utilities
    {
        return new Utilities($this);
    }

    public function getResponse($response)
    {
        return $response['choices'][0]['message']['content'];
    }

    /**
     * @param $response
     * @return string
     */
    public function getStreamedText($response)
    {
        return $response['choices'][0]['delta']['content'] ?? "";
    }

    /**
     * @param $response
     * @return array
     */
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

    /**
     * @param $response
     * @return bool
     */
    public function validate($response): bool
    {
        return !($response['object'] == "error");
    }
}
