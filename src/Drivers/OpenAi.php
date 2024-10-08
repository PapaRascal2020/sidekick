<?php

namespace PapaRascalDev\Sidekick\Drivers;

use Generator;
use PapaRascalDev\Sidekick\Features\{Audio, Completion, Embedding, Image, Moderate, Transcribe};
use PapaRascalDev\Sidekick\SidekickDriverInterface;
use PapaRascalDev\Sidekick\Utilities\Utilities;

/**
 * Supported Models:
 *
 *- gpt-3.5-turbo
 * - gpt-4
 * - tts-1
 * - tts-1-hd
 * - dall-e-2
 * - dall-e-3
 * - whisper-1
 * - text-embedding-3-small
 * - text-embedding-3-large
 * - text-embedding-ada-002
 * - text-moderation-latest
 * - text-moderation-stable
 * - text-moderation-007
 *
 * Supported Methods
 * - Completions
 * - Embed
 * - Audio
 * - Transcription
 * - Moderate
 */

class OpenAi implements SidekickDriverInterface
{

    /**
     * Api Base URL
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
        'user' => 'user',
        'assistant' => 'assistant'
    ];

    /**
     * @var string
     */
    public string $defaultCompleteModel = "gpt-3.5-turbo";

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
        $apiToken = getenv("SIDEKICK_OPENAI_TOKEN");

        $this->headers = [
            "Authorization" => "Bearer {$apiToken}",
        ];
    }

    /**
     * @return Image
     */
    public function image(): Image
    {
        return new Image(
            url: "{$this->baseUrl}/images/generations",
            headers: $this->headers
        );
    }

    /**
     * @return Audio
     */
    public function audio(): Audio
    {
        return new Audio(
            url: "{$this->baseUrl}/audio/speech",
            headers: $this->headers
        );
    }

    /**
     * @return Transcribe
     */
    public function transcribe(): Transcribe
    {
        return new Transcribe(
            url: "{$this->baseUrl}/audio/transcriptions",
            headers: $this->headers
        );
    }

    public function complete ( string $model,
                               string $systemPrompt,
                               string $message,
                               array | object $allMessages = [],
                               int $maxTokens = 1024,
                               bool $stream = false): array|string
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
     * @return Moderate
     */
    public function moderate(): Moderate
    {
        return new Moderate(
            url: "{$this->baseUrl}/moderations",
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

    /**
     * @param $response
     * @return string|array
     */
    private function getResponse($response): string|array
    {
        if( isset( $response['error'] ) ) return $this->getErrorMessage( $response );
        return $response['choices'][0]['message']['content'];
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
    public function getErrorMessage($response): array
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

    /**
     * @param $response
     * @return bool
     */
    public function validate($response): bool
    {
        return !isset($response['error']);
    }
}
