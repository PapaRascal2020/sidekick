<?php

namespace PapaRascalDev\Sidekick\Exceptions;

use PapaRascalDev\Sidekick\Enums\Capability;

class UnsupportedCapabilityException extends SidekickException
{
    public function __construct(string $provider, Capability $capability)
    {
        parent::__construct("Provider [{$provider}] does not support the [{$capability->value}] capability.");
    }
}
