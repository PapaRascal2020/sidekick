<?php

namespace PapaRascalDev\Sidekick\Builders;

use PapaRascalDev\Sidekick\Contracts\ProvidesModeration;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Events\RequestFailed;
use PapaRascalDev\Sidekick\Events\RequestSending;
use PapaRascalDev\Sidekick\Events\ResponseReceived;
use PapaRascalDev\Sidekick\Exceptions\UnsupportedCapabilityException;
use PapaRascalDev\Sidekick\Responses\ModerationResponse;
use PapaRascalDev\Sidekick\SidekickManager;

class ModerationBuilder
{
    private ?string $provider = null;
    private ?string $model = null;
    private string $content = '';

    public function __construct(
        private readonly SidekickManager $manager,
    ) {}

    public function using(string $provider, ?string $model = null): self
    {
        $this->provider = $provider;
        $this->model = $model;

        return $this;
    }

    public function withContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function generate(): ModerationResponse
    {
        $providerName = $this->provider ?? config('sidekick.defaults.moderation.provider', 'openai');
        $model = $this->model ?? config('sidekick.defaults.moderation.model', 'text-moderation-latest');
        $this->provider = $providerName;
        $this->model = $model;

        $provider = $this->manager->provider($providerName);

        if (! $provider instanceof ProvidesModeration) {
            throw new UnsupportedCapabilityException($providerName, Capability::Moderation);
        }

        event(new RequestSending($providerName, $model, Capability::Moderation));

        try {
            $response = $provider->moderate($model, $this->content);

            event(new ResponseReceived($providerName, $model, Capability::Moderation, $response));

            return $response;
        } catch (\Throwable $e) {
            event(new RequestFailed($providerName, $model, Capability::Moderation, $e));

            throw $e;
        }
    }
}
