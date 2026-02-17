<?php

namespace PapaRascalDev\Sidekick\Providers;

use PapaRascalDev\Sidekick\Contracts\ProviderContract;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Exceptions\ConfigurationException;
use PapaRascalDev\Sidekick\Providers\Concerns\HandlesHttpRequests;
use PapaRascalDev\Sidekick\Providers\Concerns\ParsesStreamedResponses;

abstract class AbstractProvider implements ProviderContract
{
    use HandlesHttpRequests;
    use ParsesStreamedResponses;

    public function __construct(
        protected readonly array $config,
    ) {
        if (empty($this->config['api_key'])) {
            throw ConfigurationException::missingApiKey($this->name());
        }
    }

    public function supports(Capability $capability): bool
    {
        return in_array($capability, $this->capabilities());
    }
}
