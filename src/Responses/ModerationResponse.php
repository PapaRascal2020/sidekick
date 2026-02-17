<?php

namespace PapaRascalDev\Sidekick\Responses;

use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\ModerationCategory;

readonly class ModerationResponse
{
    /**
     * @param  ModerationCategory[]  $categories
     */
    public function __construct(
        public bool $flagged,
        public array $categories,
        public Meta $meta,
    ) {}

    public function isFlagged(): bool
    {
        return $this->flagged;
    }

    public function isFlaggedFor(string $category): bool
    {
        foreach ($this->categories as $cat) {
            if ($cat->category === $category && $cat->flagged) {
                return true;
            }
        }

        return false;
    }
}
