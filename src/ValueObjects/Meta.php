<?php

namespace PapaRascalDev\Sidekick\ValueObjects;

readonly class Meta
{
    public function __construct(
        public string $provider,
        public string $model,
        public ?string $requestId = null,
        public ?float $latencyMs = null,
    ) {}

    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'model' => $this->model,
            'request_id' => $this->requestId,
            'latency_ms' => $this->latencyMs,
        ];
    }
}
