<?php

namespace PapaRascalDev\Sidekick\Testing;

use PHPUnit\Framework\Assert;
use PapaRascalDev\Sidekick\Builders\AudioBuilder;
use PapaRascalDev\Sidekick\Builders\ConversationBuilder;
use PapaRascalDev\Sidekick\Builders\EmbeddingBuilder;
use PapaRascalDev\Sidekick\Builders\ImageBuilder;
use PapaRascalDev\Sidekick\Builders\ModerationBuilder;
use PapaRascalDev\Sidekick\Builders\TranscriptionBuilder;

class SidekickFake
{
    protected array $responses;
    protected array $recorded = [];
    protected int $responseIndex = 0;

    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

    public function text(): FakeTextBuilder
    {
        return new FakeTextBuilder($this);
    }

    public function image(): ImageBuilder
    {
        return new ImageBuilder($this->createDummyManager());
    }

    public function audio(): AudioBuilder
    {
        return new AudioBuilder($this->createDummyManager());
    }

    public function transcription(): TranscriptionBuilder
    {
        return new TranscriptionBuilder($this->createDummyManager());
    }

    public function embedding(): EmbeddingBuilder
    {
        return new EmbeddingBuilder($this->createDummyManager());
    }

    public function moderation(): ModerationBuilder
    {
        return new ModerationBuilder($this->createDummyManager());
    }

    public function conversation(): ConversationBuilder
    {
        return new ConversationBuilder($this->createDummyManager());
    }

    public function nextResponse(): mixed
    {
        if (isset($this->responses[$this->responseIndex])) {
            return $this->responses[$this->responseIndex++];
        }

        return $this->responses[0] ?? null;
    }

    public function record(array $data): void
    {
        $this->recorded[] = $data;
    }

    public function recorded(): array
    {
        return $this->recorded;
    }

    // ----- Assertions -----

    public function assertTextGenerated(int $times = null): self
    {
        $textCalls = array_filter($this->recorded, fn ($r) => ($r['type'] ?? '') === 'text');

        if ($times !== null) {
            Assert::assertCount($times, $textCalls, "Expected text to be generated {$times} time(s), but was generated ".count($textCalls).' time(s).');
        } else {
            Assert::assertNotEmpty($textCalls, 'Expected text to be generated at least once, but it was never generated.');
        }

        return $this;
    }

    public function assertNothingSent(): self
    {
        Assert::assertEmpty($this->recorded, 'Expected nothing to be sent, but '.count($this->recorded).' request(s) were recorded.');

        return $this;
    }

    public function assertProviderUsed(string $provider): self
    {
        $found = array_filter($this->recorded, fn ($r) => ($r['provider'] ?? '') === $provider);

        Assert::assertNotEmpty($found, "Expected provider [{$provider}] to be used, but it was not.");

        return $this;
    }

    public function assertModelUsed(string $model): self
    {
        $found = array_filter($this->recorded, fn ($r) => ($r['model'] ?? '') === $model);

        Assert::assertNotEmpty($found, "Expected model [{$model}] to be used, but it was not.");

        return $this;
    }

    public function assertPromptContains(string $text): self
    {
        $found = array_filter($this->recorded, function ($r) use ($text) {
            $prompt = $r['prompt'] ?? '';

            return str_contains($prompt, $text);
        });

        Assert::assertNotEmpty($found, "Expected a prompt containing [{$text}], but none was found.");

        return $this;
    }

    private function createDummyManager(): \PapaRascalDev\Sidekick\SidekickManager
    {
        return app('sidekick');
    }
}
