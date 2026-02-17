<?php

namespace PapaRascalDev\Sidekick\Responses;

use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

readonly class EmbeddingResponse
{
    public function __construct(
        public array $embeddings,
        public Usage $usage,
        public Meta $meta,
    ) {}

    public function vector(): array
    {
        return $this->embeddings[0] ?? [];
    }
}
