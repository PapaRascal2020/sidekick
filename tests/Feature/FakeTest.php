<?php

namespace PapaRascalDev\Sidekick\Tests\Feature;

use PapaRascalDev\Sidekick\Facades\Sidekick;
use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\Testing\SidekickFake;
use PapaRascalDev\Sidekick\Tests\TestCase;
use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

class FakeTest extends TestCase
{
    public function test_fake_returns_sidekick_fake_instance(): void
    {
        $fake = Sidekick::fake();

        $this->assertInstanceOf(SidekickFake::class, $fake);
    }

    public function test_fake_returns_queued_text_response(): void
    {
        $expectedResponse = new TextResponse(
            text: 'Mocked response',
            usage: new Usage(5, 10, 15),
            meta: new Meta('openai', 'gpt-4o'),
            finishReason: 'stop',
        );

        $fake = Sidekick::fake([$expectedResponse]);

        $response = $fake->text()
            ->using('openai', 'gpt-4o')
            ->withPrompt('Hello')
            ->generate();

        $this->assertEquals('Mocked response', $response->text);
        $this->assertEquals(15, $response->usage->totalTokens);
    }

    public function test_fake_assert_text_generated(): void
    {
        $fake = Sidekick::fake([
            new TextResponse(
                text: 'test',
                usage: new Usage(),
                meta: new Meta('openai', 'gpt-4o'),
            ),
        ]);

        $fake->text()->using('openai', 'gpt-4o')->withPrompt('Hello')->generate();

        $fake->assertTextGenerated();
        $fake->assertTextGenerated(1);
    }

    public function test_fake_assert_nothing_sent(): void
    {
        $fake = Sidekick::fake();

        $fake->assertNothingSent();
    }

    public function test_fake_assert_provider_used(): void
    {
        $fake = Sidekick::fake([
            new TextResponse(text: 'ok', usage: new Usage(), meta: new Meta('openai', 'gpt-4o')),
        ]);

        $fake->text()->using('openai', 'gpt-4o')->withPrompt('test')->generate();

        $fake->assertProviderUsed('openai');
    }

    public function test_fake_assert_model_used(): void
    {
        $fake = Sidekick::fake([
            new TextResponse(text: 'ok', usage: new Usage(), meta: new Meta('openai', 'gpt-4o')),
        ]);

        $fake->text()->using('openai', 'gpt-4o')->withPrompt('test')->generate();

        $fake->assertModelUsed('gpt-4o');
    }

    public function test_fake_assert_prompt_contains(): void
    {
        $fake = Sidekick::fake([
            new TextResponse(text: 'ok', usage: new Usage(), meta: new Meta('openai', 'gpt-4o')),
        ]);

        $fake->text()->using('openai', 'gpt-4o')->withPrompt('Tell me about Laravel')->generate();

        $fake->assertPromptContains('Laravel');
    }

    public function test_fake_text_stream(): void
    {
        $fake = Sidekick::fake([
            new TextResponse(text: 'streamed text', usage: new Usage(), meta: new Meta('openai', 'gpt-4o')),
        ]);

        $stream = $fake->text()->using('openai', 'gpt-4o')->withPrompt('test')->stream();

        $text = '';
        foreach ($stream as $chunk) {
            $text .= $chunk;
        }

        $this->assertEquals('streamed text', $text);
    }
}
