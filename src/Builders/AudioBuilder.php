<?php

namespace PapaRascalDev\Sidekick\Builders;

use PapaRascalDev\Sidekick\Contracts\ProvidesAudio;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Events\RequestFailed;
use PapaRascalDev\Sidekick\Events\RequestSending;
use PapaRascalDev\Sidekick\Events\ResponseReceived;
use PapaRascalDev\Sidekick\Exceptions\UnsupportedCapabilityException;
use PapaRascalDev\Sidekick\Responses\AudioResponse;
use PapaRascalDev\Sidekick\SidekickManager;

class AudioBuilder
{
    private ?string $provider = null;
    private ?string $model = null;
    private string $text = '';
    private string $voice = 'alloy';
    private string $format = 'mp3';

    public function __construct(
        private readonly SidekickManager $manager,
    ) {}

    public function using(string $provider, ?string $model = null): self
    {
        $this->provider = $provider;
        $this->model = $model;

        return $this;
    }

    public function withText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function withVoice(string $voice): self
    {
        $this->voice = $voice;

        return $this;
    }

    public function withFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function generate(): AudioResponse
    {
        $providerName = $this->provider ?? config('sidekick.defaults.audio.provider', 'openai');
        $model = $this->model ?? config('sidekick.defaults.audio.model', 'tts-1');
        $this->provider = $providerName;
        $this->model = $model;

        $provider = $this->manager->provider($providerName);

        if (! $provider instanceof ProvidesAudio) {
            throw new UnsupportedCapabilityException($providerName, Capability::Audio);
        }

        event(new RequestSending($providerName, $model, Capability::Audio));

        try {
            $response = $provider->generateAudio($model, $this->text, $this->voice, $this->format);

            event(new ResponseReceived($providerName, $model, Capability::Audio, $response));

            return $response;
        } catch (\Throwable $e) {
            event(new RequestFailed($providerName, $model, Capability::Audio, $e));

            throw $e;
        }
    }
}
