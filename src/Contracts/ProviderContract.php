<?php

namespace PapaRascalDev\Sidekick\Contracts;

use PapaRascalDev\Sidekick\Enums\Capability;

interface ProviderContract
{
    public function name(): string;

    /**
     * @return Capability[]
     */
    public function capabilities(): array;

    public function supports(Capability $capability): bool;
}
