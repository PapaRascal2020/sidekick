<?php

namespace PapaRascalDev\Sidekick\Drivers;

use PapaRascalDev\Sidekick\Features\Completion;
use PapaRascalDev\Sidekick\Features\StreamedCompletion;

/**
 * Unlike other AI's passing no model sets a defaults
 * to Command-r
 *
 * Supported Models:
 *
 *- command-r-08-2024
 *- command-r-plus-08-2024
 *
 * Supported Methods
 * - Completions
 */

class Cohere implements Driver
{

    /**
     * Api Base URL
     * @strind $baseUrl
     */
    private string $baseUrl = "https://api.cohere.com/v1";

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
        'user' => 'USER',
        'assistant' => 'CHATBOT'
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

    public bool $listAsObject = true;

    public array $chatMaps = [
        'content' => 'message'
    ];


    function __construct()
    {
        $apiToken = getenv("SIDEKICK_COHERE_TOKEN");

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
            url: "{$this->baseUrl}/chat",
            headers: $this->headers,
            requestRules: [
                'chat_history' => '$allMessages ? $allMessages : null',
                'message' => '$message'
            ]
        );
    }

    /**
     * @return StreamedCompletion
     */
    public function completeStreamed(): StreamedCompletion
    {
        return new StreamedCompletion(
            url: "{$this->baseUrl}/chat",
            headers: $this->headers,
            requestRules: [
                'chat_history' => '$allMessages ? $allMessages : null',
                'message' => '$message'
            ]
        );
    }


    /**
     * @param $response
     * @return mixed
     */
    public function getResponse($response)
    {
        return $response['text'] ?? "";
    }

    /**
     * @param $response
     * @return string
     */
    public function getStreamedText($response)
    {
        return $response['text'] ?? "";
    }

    /**
     * @param $response
     * @return array
     */
    public function getErrorMessage($response)
    {
        return [
            'driver' => 'Cohere',
            'error' => [
                'type' => 'error',
                'code' => null,
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
        return !(isset($response['message']));
    }
}
