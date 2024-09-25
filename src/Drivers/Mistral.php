<?php

namespace PapaRascalDev\Sidekick\Drivers;

use PapaRascalDev\Sidekick\Features\{Completion, Embedding};
use Generator;
use PapaRascalDev\Sidekick\SidekickDriverInterface;
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

class Mistral implements SidekickDriverInterface
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


    public function complete ( string $model,
                               string $systemPrompt,
                               string $message,
                               array | object $allMessages = [],
                               int $maxTokens = 1024,
                               bool $stream = false)
    {

        $completion = (new Completion(
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

    private function getResponse($response)
    {
        if( $response['object'] == "error" ) return $this->getErrorMessage( $response );
        return $response['choices'][0]['message']['content'];
    }

    private function getResponseStreamed($response)
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
                $message .= $chunk['choices'][0]['delta']['content'] ?? "";
                echo $chunk['choices'][0]['delta']['content'] ?? "";
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
}
