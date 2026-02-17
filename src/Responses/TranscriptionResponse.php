<?php

namespace PapaRascalDev\Sidekick\Responses;

use PapaRascalDev\Sidekick\ValueObjects\Meta;

readonly class TranscriptionResponse
{
    public function __construct(
        public string $text,
        public Meta $meta,
        public ?string $language = null,
        public ?float $duration = null,
    ) {}

    public function __toString(): string
    {
        return $this->text;
    }
}
