<?php

namespace PapaRascalDev\Sidekick\Builders;

use PapaRascalDev\Sidekick\Contracts\ProvidesImages;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Events\RequestFailed;
use PapaRascalDev\Sidekick\Events\RequestSending;
use PapaRascalDev\Sidekick\Events\ResponseReceived;
use PapaRascalDev\Sidekick\Exceptions\UnsupportedCapabilityException;
use PapaRascalDev\Sidekick\Responses\ImageResponse;
use PapaRascalDev\Sidekick\SidekickManager;

class ImageBuilder
{
    private ?string $provider = null;
    private ?string $model = null;
    private string $prompt = '';
    private string $size = '1024x1024';
    private string $quality = 'standard';
    private int $count = 1;

    public function __construct(
        private readonly SidekickManager $manager,
    ) {}

    public function using(string $provider, ?string $model = null): self
    {
        $this->provider = $provider;
        $this->model = $model;

        return $this;
    }

    public function withPrompt(string $prompt): self
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function withSize(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function withQuality(string $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function count(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function generate(): ImageResponse
    {
        $providerName = $this->provider ?? config('sidekick.defaults.image.provider', 'openai');
        $model = $this->model ?? config('sidekick.defaults.image.model', 'dall-e-3');
        $this->provider = $providerName;
        $this->model = $model;

        $provider = $this->manager->provider($providerName);

        if (! $provider instanceof ProvidesImages) {
            throw new UnsupportedCapabilityException($providerName, Capability::Image);
        }

        event(new RequestSending($providerName, $model, Capability::Image));

        try {
            $response = $provider->generateImage($model, $this->prompt, $this->size, $this->quality, $this->count);

            event(new ResponseReceived($providerName, $model, Capability::Image, $response));

            return $response;
        } catch (\Throwable $e) {
            event(new RequestFailed($providerName, $model, Capability::Image, $e));

            throw $e;
        }
    }
}
