<?php

namespace PapaRascalDev\Sidekick\Builders;

use PapaRascalDev\Sidekick\Contracts\ProvidesTranscription;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Events\RequestFailed;
use PapaRascalDev\Sidekick\Events\RequestSending;
use PapaRascalDev\Sidekick\Events\ResponseReceived;
use PapaRascalDev\Sidekick\Exceptions\UnsupportedCapabilityException;
use PapaRascalDev\Sidekick\Responses\TranscriptionResponse;
use PapaRascalDev\Sidekick\SidekickManager;

class TranscriptionBuilder
{
    private ?string $provider = null;
    private ?string $model = null;
    private string $filePath = '';
    private ?string $language = null;

    public function __construct(
        private readonly SidekickManager $manager,
    ) {}

    public function using(string $provider, ?string $model = null): self
    {
        $this->provider = $provider;
        $this->model = $model;

        return $this;
    }

    public function withFile(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function withLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function generate(): TranscriptionResponse
    {
        $providerName = $this->provider ?? config('sidekick.defaults.transcription.provider', 'openai');
        $model = $this->model ?? config('sidekick.defaults.transcription.model', 'whisper-1');
        $this->provider = $providerName;
        $this->model = $model;

        $provider = $this->manager->provider($providerName);

        if (! $provider instanceof ProvidesTranscription) {
            throw new UnsupportedCapabilityException($providerName, Capability::Transcription);
        }

        event(new RequestSending($providerName, $model, Capability::Transcription));

        try {
            $response = $provider->transcribe($model, $this->filePath, $this->language);

            event(new ResponseReceived($providerName, $model, Capability::Transcription, $response));

            return $response;
        } catch (\Throwable $e) {
            event(new RequestFailed($providerName, $model, Capability::Transcription, $e));

            throw $e;
        }
    }
}
