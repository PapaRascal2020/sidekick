<?php

namespace PapaRascalDev\Sidekick;

/**
 * Sidekick Driver interface
 *
 * This enforces the common functions we expect
 * from a AI driver.
 *
 */

interface SidekickDriverInterface
{
    public function complete( string $model, string $systemPrompt, string $message, array $allMessages, int $maxTokens, bool $stream);

}
