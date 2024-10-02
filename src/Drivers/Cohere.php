<?php

namespace PapaRascalDev\Sidekick\Drivers;

use Generator;
use PapaRascalDev\Sidekick\Features\Completion;
use PapaRascalDev\Sidekick\SidekickDriverInterface;
use PapaRascalDev\Sidekick\Utilities\Utilities;

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

class Cohere implements SidekickDriverInterface
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
     * user and bot roles so added this, so it
     * can be specified.
     *
     * @array $messageRoles
     */
    public array $messageRoles = [
        'user' => 'USER',
        'assistant' => 'CHATBOT'
    ];

    /**
     * @var string
     */
    public string $defaultCompleteModel = "command-r-plus-08-2024";

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

    public function complete ( string $model,
                               string $systemPrompt,
                               string $message,
                               array | object $allMessages = [],
                               int $maxTokens = 1024,
                               bool $stream = false)
    {

        $completion = (new Completion(
            url: "{$this->baseUrl}/chat",
            headers: $this->headers,
            requestRules: [
                'chat_history' => '$allMessages ? $allMessages : null',
                'message' => '$message'
            ]
        ));

        if ( $stream )
        {
            return $this->getResponseStreamed($completion->sendMessage(
                model: $model,
                systemPrompt: $systemPrompt,
                message: $message,
                allMessages: $allMessages,
                maxTokens: $maxTokens,
                stream: true
            ));
        } else {
            $response =  $completion->sendMessage(
                model: $model,
                systemPrompt: $systemPrompt,
                message: $message,
                allMessages: $allMessages,
                maxTokens: $maxTokens
            );

            return $this->getResponse($response);
        }
    }

    /**
     * @return Utilities
     */
    public function utilities(): Utilities
    {
        return new Utilities($this);
    }


    /**
     * @param $response
     * @return mixed
     */
    public function getResponse($response): mixed
    {
        if( isset($response['message']) ) return $this->getErrorMessage( $response );
        return $response['text'] ?? "";
    }

    private function getResponseStreamed($response): string
    {
        // Set the headers for a streamed response
        header('HTTP/1.0 200 OK');
        header('Cache-Control: no-cache, private');
        header('Date: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('X-Accel-Buffering: no');

        // Flush the headers to the client
        ob_flush();
        flush();

        // Stream the response content
        if ($response instanceof Generator) {
            $message = "";
            foreach ($response as $chunk) {
                $message .= $chunk['text'] ?? "";
                echo $chunk['text'] ?? "";
                ob_flush();
                flush();
            }
        }

        // Ensure all output is sent to the client
        ob_flush();
        flush();

        return $message;
    }

    /**
     * @param $response
     * @return array
     */
    public function getErrorMessage($response): array
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
