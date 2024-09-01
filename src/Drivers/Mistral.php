<?php

namespace PapaRascalDev\Sidekick\Drivers;

use PapaRascalDev\Sidekick\Features\{Completion, Embedding};

class Mistral implements Driver
{
    protected array $config;
    function __construct()
    {
        $this->config = config('sidekick.config.driver.Mistral');
    }

    /**
     * @return Completion
     */
    public function complete(): Completion
    {
        return new Completion(
            url: $this->config['baseUrl'].$this->config['services']['completion'],
            headers: $this->config['headers']
        );
    }

    /**
     * @return Embedding
     */
    public function embedding(): Embedding
    {
        return new Embedding(
            url: $this->config['baseUrl'].$this->config['services']['embedding'],
            headers: $this->config['headers']
        );
    }

    public function uniformedResponse($response)
    {
        return $response['choices'][0]['message']['content'];
    }
}
