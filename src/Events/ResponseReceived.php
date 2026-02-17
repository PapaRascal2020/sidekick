<?php

namespace PapaRascalDev\Sidekick\Events;

use Illuminate\Foundation\Events\Dispatchable;
use PapaRascalDev\Sidekick\Enums\Capability;

class ResponseReceived
{
    use Dispatchable;

    public function __construct(
        public readonly ?string $provider,
        public readonly ?string $model,
        public readonly Capability $capability,
        public readonly mixed $response,
    ) {}
}
