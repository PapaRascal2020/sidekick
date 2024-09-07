<?php

namespace PapaRascalDev\Sidekick\Drivers;

use PapaRascalDev\Sidekick\Features\Completion;

class Cohere implements Driver
{

    /**
     * OpenAi Api Base URL
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

    public array $messageRoles = [
        'user' => 'USER',
        'assistant' => 'CHATBOT'
    ];

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
        return $response['text'];
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
