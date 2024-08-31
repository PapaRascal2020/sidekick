<?php

namespace PapaRascalDev\Sidekick\Drivers;

use PapaRascalDev\Sidekick\Features\Completion;

class Claude implements Driver
{
    protected array $config;

    function __construct()
    {
        $this->config = config('sidekick.config.driver.Claude');
    }

    /**
     * @return Completion
     */
    public function converse(): Completion
    {
        return new Completion(
            url: $this->config['baseUrl'].$this->config['services']['completion'],
            headers: $this->config['headers'],
            inlinePrompt: false,
            submitTypes: true
        );
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
}
