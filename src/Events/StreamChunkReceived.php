<?php

namespace PapaRascalDev\Sidekick\Events;

use Illuminate\Foundation\Events\Dispatchable;

class StreamChunkReceived
{
    use Dispatchable;

    public function __construct(
        public readonly ?string $provider,
        public readonly ?string $model,
        public readonly string $chunk,
    ) {}
}
