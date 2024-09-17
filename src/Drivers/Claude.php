<?php

namespace PapaRascalDev\Sidekick\Drivers;

use PapaRascalDev\Sidekick\Features\Completion;
use PapaRascalDev\Sidekick\Features\StreamedCompletion;

/**
 * Supported Models:
 *
 * - claude-3-opus-20240229
 * - claude-3-sonnet-20240229
 * - claude-3-haiku-20240307
 *
 * Supported Methods
 * - Completions
 */

class Claude implements Driver
{
    /**
     * Api Base URL
     * @strind $baseUrl
     */
    private string $baseUrl = "https://api.anthropic.com/v1";

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
        $this->headers = [
            'anthropic-version' => '2023-06-01',
            'x-api-key' => getenv('SIDEKICK_CLAUDE_TOKEN')
        ];
    }

    /**
     * @return Completion
     */
    public function complete(): Completion
    {
        return new Completion(
            url: "{$this->baseUrl}/messages",
            headers: $this->headers,
            requestRules: [
                'model' => '$model',
                'max_tokens' => '$maxTokens',
                'system' => '$systemPrompt ?? null',
                'messages' => [
                    '$allMessages ? $allMessages : null',
                    '["role" => "user", "content" => $message]',
                ]
            ]
        );
    }

    /**
     * @return StreamedCompletion
     */
    public function completeStreamed(): StreamedCompletion
    {
        return new StreamedCompletion(
            url: "{$this->baseUrl}/messages",
            headers: $this->headers,
            requestRules: [
                'model' => '$model',
                'max_tokens' => '$maxTokens',
                'system' => '$systemPrompt ?? null',
                'messages' => [
                    '$allMessages ? $allMessages : null',
                    '["role" => "user", "content" => $message]',
                ]
            ]
        );
    }

    /**
     * @param $response
     * @return mixed
     */
    public function getResponse($response)
    {
        return $response['content'][0]['text'];
    }

    /**
     * @param $response
     * @return string
     */
    public function getStreamedText($response)
    {
        return $response['delta']['text'] ?? "";
    }

    /**
     * @param $response
     * @return array
     */
    public function getErrorMessage($response)
    {
        return [
            'driver' => 'Claude',
            'error' => [
                'type' => $response['type'],
                'code' => $response['error']['type'],
                'message' => $response['error']['message']
            ]
        ];
    }

    /**
     * @param $response
     * @return bool
     */
    public function validate($response): bool
    {
        return !($response['type'] == "error");
    }
}
