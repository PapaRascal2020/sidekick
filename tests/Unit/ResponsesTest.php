<?php

namespace PapaRascalDev\Sidekick\Tests\Unit;

use PapaRascalDev\Sidekick\Responses\EmbeddingResponse;
use PapaRascalDev\Sidekick\Responses\ImageResponse;
use PapaRascalDev\Sidekick\Responses\ModerationResponse;
use PapaRascalDev\Sidekick\Responses\StreamResponse;
use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\Tests\TestCase;
use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\ModerationCategory;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

class ResponsesTest extends TestCase
{
    public function test_text_response_to_string(): void
    {
        $response = new TextResponse(
            text: 'Hello world',
            usage: new Usage(5, 10, 15),
            meta: new Meta('openai', 'gpt-4o'),
            finishReason: 'stop',
        );

        $this->assertEquals('Hello world', (string) $response);
        $this->assertEquals('Hello world', $response->text);
        $this->assertEquals(15, $response->usage->totalTokens);
        $this->assertEquals('stop', $response->finishReason);
    }

    public function test_image_response_url_helper(): void
    {
        $response = new ImageResponse(
            urls: ['https://example.com/image1.png', 'https://example.com/image2.png'],
            meta: new Meta('openai', 'dall-e-3'),
            revisedPrompt: 'A revised prompt',
        );

        $this->assertEquals('https://example.com/image1.png', $response->url());
        $this->assertCount(2, $response->urls);
        $this->assertEquals('A revised prompt', $response->revisedPrompt);
    }

    public function test_image_response_url_returns_null_for_empty(): void
    {
        $response = new ImageResponse(
            urls: [],
            meta: new Meta('openai', 'dall-e-3'),
        );

        $this->assertNull($response->url());
    }

    public function test_embedding_response_vector_helper(): void
    {
        $response = new EmbeddingResponse(
            embeddings: [[0.1, 0.2, 0.3], [0.4, 0.5, 0.6]],
            usage: new Usage(5, 0, 5),
            meta: new Meta('openai', 'text-embedding-3-small'),
        );

        $this->assertEquals([0.1, 0.2, 0.3], $response->vector());
        $this->assertCount(2, $response->embeddings);
    }

    public function test_moderation_response(): void
    {
        $response = new ModerationResponse(
            flagged: true,
            categories: [
                new ModerationCategory('violence', true, 0.95),
                new ModerationCategory('hate', false, 0.01),
            ],
            meta: new Meta('openai', 'text-moderation-latest'),
        );

        $this->assertTrue($response->isFlagged());
        $this->assertTrue($response->isFlaggedFor('violence'));
        $this->assertFalse($response->isFlaggedFor('hate'));
        $this->assertFalse($response->isFlaggedFor('nonexistent'));
    }

    public function test_stream_response_iteration(): void
    {
        $generator = (function () {
            yield 'Hello';
            yield ' ';
            yield 'World';
        })();

        $stream = new StreamResponse($generator);
        $result = '';

        foreach ($stream as $chunk) {
            $result .= $chunk;
        }

        $this->assertEquals('Hello World', $result);
    }

    public function test_stream_response_text_method(): void
    {
        $generator = (function () {
            yield 'Hello';
            yield ' World';
        })();

        $stream = new StreamResponse($generator);

        $this->assertEquals('Hello World', $stream->text());
    }

    public function test_stream_response_to_response(): void
    {
        $generator = (function () {
            yield 'chunk';
        })();

        $stream = new StreamResponse($generator);
        $response = $stream->toResponse();

        $this->assertEquals('text/event-stream', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control'));
    }
}
