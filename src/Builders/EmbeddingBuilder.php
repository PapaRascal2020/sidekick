<?php

namespace PapaRascalDev\Sidekick\Builders;

use PapaRascalDev\Sidekick\Contracts\ProvidesEmbeddings;
use PapaRascalDev\Sidekick\Enums\Capability;
use PapaRascalDev\Sidekick\Events\RequestFailed;
use PapaRascalDev\Sidekick\Events\RequestSending;
use PapaRascalDev\Sidekick\Events\ResponseReceived;
use PapaRascalDev\Sidekick\Exceptions\UnsupportedCapabilityException;
use PapaRascalDev\Sidekick\Responses\EmbeddingResponse;
use PapaRascalDev\Sidekick\SidekickManager;

class EmbeddingBuilder
{
    private ?string $provider = null;
    private ?string $model = null;
    private string|array $input = '';

    public function __construct(
        private readonly SidekickManager $manager,
    ) {}

    public function using(string $provider, ?string $model = null): self
    {
        $this->provider = $provider;
        $this->model = $model;

        return $this;
    }

    public function withInput(string|array $input): self
    {
        $this->input = $input;

        return $this;
    }

    public function generate(): EmbeddingResponse
    {
        $providerName = $this->provider ?? config('sidekick.defaults.embedding.provider', 'openai');
        $model = $this->model ?? config('sidekick.defaults.embedding.model', 'text-embedding-3-small');
        $this->provider = $providerName;
        $this->model = $model;

        $provider = $this->manager->provider($providerName);

        if (! $provider instanceof ProvidesEmbeddings) {
            throw new UnsupportedCapabilityException($providerName, Capability::Embedding);
        }

        event(new RequestSending($providerName, $model, Capability::Embedding));

        try {
            $response = $provider->generateEmbedding($model, $this->input);

            event(new ResponseReceived($providerName, $model, Capability::Embedding, $response));

            return $response;
        } catch (\Throwable $e) {
            event(new RequestFailed($providerName, $model, Capability::Embedding, $e));

            throw $e;
        }
    }
}
