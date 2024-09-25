<?php

namespace PapaRascalDev\Sidekick\Drivers;

use Generator;
use PapaRascalDev\Sidekick\Features\Completion;
use PapaRascalDev\Sidekick\Features\StreamedCompletion;
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
 */

class Claude implements SidekickDriverInterface
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
     * @var string
     */
    public string $defaultCompleteModel = "claude-3-sonnet-20240229";


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
            'x-api-key' => getenv("SIDEKICK_CLAUDE_TOKEN")
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
    public function getResponse($response)
    {
        if( $response['type'] === "error" ) return $this->getErrorMessage( $response );
        return $response['content'][0]['text'];
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
                $message .= $chunk['delta']['text'] ?? "";
                echo $chunk['delta']['text'] ?? "";
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
            'driver' => 'Claude',
            'error' => [
                'type' => $response['type'],
                'code' => $response['error']['type'],
                'message' => $response['error']['message']
            ]
        ];
    }
}
