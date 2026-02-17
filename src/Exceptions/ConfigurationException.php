<?php

namespace PapaRascalDev\Sidekick\Exceptions;

class ConfigurationException extends SidekickException
{
    public static function missingApiKey(string $provider): self
    {
        return new self("API key for provider [{$provider}] is not configured. Please set it in your .env file or config/sidekick.php.");
    }

    public static function invalidProvider(string $provider): self
    {
        return new self("Provider [{$provider}] is not configured. Check your config/sidekick.php file.");
    }
}
