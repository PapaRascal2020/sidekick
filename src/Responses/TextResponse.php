<?php

namespace PapaRascalDev\Sidekick\Responses;

use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

readonly class TextResponse
{
    public function __construct(
        public string $text,
        public Usage $usage,
        public Meta $meta,
        public ?string $finishReason = null,
    ) {}

    public function __toString(): string
    {
        return $this->text;
    }
}
