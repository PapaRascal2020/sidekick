<?php

namespace PapaRascalDev\Sidekick\Responses;

use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\ToolCall;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

readonly class TextResponse
{
    /**
     * @param  ToolCall[]  $toolCalls
     * @param  array<string, mixed>|null  $structured
     */
    public function __construct(
        public string $text,
        public Usage $usage,
        public Meta $meta,
        public ?string $finishReason = null,
        public array $toolCalls = [],
        public ?array $structured = null,
    ) {}

    public function hasToolCalls(): bool
    {
        return $this->toolCalls !== [];
    }

    public function hasStructured(): bool
    {
        return $this->structured !== null;
    }

    public function __toString(): string
    {
        return $this->text;
    }
}
