<?php

namespace PapaRascalDev\Sidekick\ValueObjects;

readonly class Usage
{
    public function __construct(
        public int $promptTokens = 0,
        public int $completionTokens = 0,
        public int $totalTokens = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            promptTokens: $data['prompt_tokens'] ?? $data['input_tokens'] ?? 0,
            completionTokens: $data['completion_tokens'] ?? $data['output_tokens'] ?? 0,
            totalTokens: $data['total_tokens'] ?? (($data['prompt_tokens'] ?? $data['input_tokens'] ?? 0) + ($data['completion_tokens'] ?? $data['output_tokens'] ?? 0)),
        );
    }

    public function toArray(): array
    {
        return [
            'prompt_tokens' => $this->promptTokens,
            'completion_tokens' => $this->completionTokens,
            'total_tokens' => $this->totalTokens,
        ];
    }
}
