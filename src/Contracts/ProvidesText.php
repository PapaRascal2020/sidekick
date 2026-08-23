<?php

namespace PapaRascalDev\Sidekick\Contracts;

use Generator;
use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\ValueObjects\Message;
use PapaRascalDev\Sidekick\ValueObjects\Schema;
use PapaRascalDev\Sidekick\ValueObjects\Tool;

interface ProvidesText
{
    /**
     * @param  Message[]  $messages
     * @param  Tool[]  $tools
     */
    public function generateText(string $model, array $messages, ?string $systemPrompt = null, int $maxTokens = 1024, float $temperature = 1.0, array $tools = [], ?Schema $schema = null): TextResponse;

    /**
     * @param  Message[]  $messages
     */
    public function streamText(string $model, array $messages, ?string $systemPrompt = null, int $maxTokens = 1024, float $temperature = 1.0): Generator;

    /**
     * Build the provider-native follow-up messages to send after tools have run.
     *
     * @param  array<int, array{id: ?string, name: string, output: string}>  $results
     * @return array<int, array<string, mixed>>
     */
    public function toolResultMessages(TextResponse $response, array $results): array;
}
