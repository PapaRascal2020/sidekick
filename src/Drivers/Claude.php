<?php

namespace PapaRascalDev\Sidekick\Drivers;

use PapaRascalDev\Sidekick\Features\Completion;

class Claude implements Driver
{

    /**
     * OpenAi Api Base URL
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

    public array $messageRoles = [
        'user' => 'user',
        'assistant' => 'assistant'
    ];

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
            ],
            responseFormat: []
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
