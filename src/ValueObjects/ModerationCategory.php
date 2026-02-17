<?php

namespace PapaRascalDev\Sidekick\ValueObjects;

readonly class ModerationCategory
{
    public function __construct(
        public string $category,
        public bool $flagged,
        public ?float $score = null,
    ) {}

    public function toArray(): array
    {
        return [
            'category' => $this->category,
            'flagged' => $this->flagged,
            'score' => $this->score,
        ];
    }
}
