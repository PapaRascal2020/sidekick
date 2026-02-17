<?php

namespace PapaRascalDev\Sidekick\Contracts;

use Generator;
use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\ValueObjects\Message;

interface ProvidesText
{
    /**
     * @param  Message[]  $messages
     */
    public function generateText(string $model, array $messages, ?string $systemPrompt = null, int $maxTokens = 1024, float $temperature = 1.0): TextResponse;

    /**
     * @param  Message[]  $messages
     */
    public function streamText(string $model, array $messages, ?string $systemPrompt = null, int $maxTokens = 1024, float $temperature = 1.0): Generator;
}
