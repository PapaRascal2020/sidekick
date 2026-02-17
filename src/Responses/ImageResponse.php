<?php

namespace PapaRascalDev\Sidekick\Responses;

use PapaRascalDev\Sidekick\ValueObjects\Meta;

readonly class ImageResponse
{
    public function __construct(
        public array $urls,
        public Meta $meta,
        public ?string $revisedPrompt = null,
    ) {}

    public function url(): ?string
    {
        return $this->urls[0] ?? null;
    }
}
