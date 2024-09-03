<?php

namespace PapaRascalDev\Sidekick\Drivers;

use PapaRascalDev\Sidekick\Features\Completion;

class Claude implements Driver
{
    protected array $config;

    /**
     * Grabs the config.
     */
    function __construct()
    {
        $this->config = config('sidekick.config.driver.Claude');
    }

    /**
     * @return Completion
     */
    public function complete(): Completion
    {
        return new Completion(
            url: $this->config['baseUrl'].$this->config['services']['completion'],
            headers: $this->config['headers'],
            inlinePrompt: false,
            submitTypes: true
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
