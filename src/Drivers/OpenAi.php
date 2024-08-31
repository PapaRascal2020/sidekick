<?php

namespace PapaRascalDev\Sidekick\Drivers;

use PapaRascalDev\Sidekick\Features\{Completion, Audio, Transcribe, Image, Embedding, Moderate};

class OpenAi implements Driver
{
    protected array $config;
    function __construct()
    {
        $this->config = config('sidekick.config.driver.OpenAi');
    }
    public function image(): Image
    {
        return new Image(
            url: $this->config['baseUrl'].$this->config['services']['image'],
            headers: $this->config['headers']
        );
    }

    public function audio(): Audio
    {
        return new Audio(
            url: $this->config['baseUrl'].$this->config['services']['audio'],
            headers: $this->config['headers']
        );
    }

    public function transcribe(): Transcribe
    {
        return new Transcribe(
            url: $this->config['baseUrl'].$this->config['services']['transcription'],
            headers: $this->config['headers']
        );
    }

    public function complete(): Completion
    {
        return new Completion(
            url: $this->config['baseUrl'].$this->config['services']['completion'],
            headers: $this->config['headers']
        );
    }
    public function converse(): Completion
    {
        return $this->complete();
    }

    public function embedding(): Embedding
    {
        return new Embedding(
            url: $this->config['baseUrl'].$this->config['services']['embedding'],
            headers: $this->config['headers']
        );
    }

    public function moderate(): Moderate
    {
        return new Moderate(
            url: $this->config['baseUrl'].$this->config['services']['moderate'],
            headers: $this->config['headers']
        );
    }
}
