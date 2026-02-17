<?php

namespace PapaRascalDev\Sidekick\Tests\Unit;

use PapaRascalDev\Sidekick\Enums\Role;
use PapaRascalDev\Sidekick\Tests\TestCase;
use PapaRascalDev\Sidekick\ValueObjects\Message;
use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\ModerationCategory;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

class ValueObjectsTest extends TestCase
{
    public function test_usage_from_array(): void
    {
        $usage = Usage::fromArray([
            'prompt_tokens' => 10,
            'completion_tokens' => 20,
            'total_tokens' => 30,
        ]);

        $this->assertEquals(10, $usage->promptTokens);
        $this->assertEquals(20, $usage->completionTokens);
        $this->assertEquals(30, $usage->totalTokens);
    }

    public function test_usage_from_anthropic_format(): void
    {
        $usage = Usage::fromArray([
            'input_tokens' => 15,
            'output_tokens' => 25,
        ]);

        $this->assertEquals(15, $usage->promptTokens);
        $this->assertEquals(25, $usage->completionTokens);
        $this->assertEquals(40, $usage->totalTokens);
    }

    public function test_usage_to_array(): void
    {
        $usage = new Usage(10, 20, 30);

        $this->assertEquals([
            'prompt_tokens' => 10,
            'completion_tokens' => 20,
            'total_tokens' => 30,
        ], $usage->toArray());
    }

    public function test_meta_creation(): void
    {
        $meta = new Meta(
            provider: 'openai',
            model: 'gpt-4o',
            requestId: 'req-123',
            latencyMs: 150.5,
        );

        $this->assertEquals('openai', $meta->provider);
        $this->assertEquals('gpt-4o', $meta->model);
        $this->assertEquals('req-123', $meta->requestId);
        $this->assertEquals(150.5, $meta->latencyMs);
    }

    public function test_message_creation_and_array_conversion(): void
    {
        $message = new Message(Role::User, 'Hello');

        $this->assertEquals(Role::User, $message->role);
        $this->assertEquals('Hello', $message->content);
        $this->assertEquals(['role' => 'user', 'content' => 'Hello'], $message->toArray());
    }

    public function test_message_from_array(): void
    {
        $message = Message::fromArray(['role' => 'assistant', 'content' => 'Hi there']);

        $this->assertEquals(Role::Assistant, $message->role);
        $this->assertEquals('Hi there', $message->content);
    }

    public function test_moderation_category(): void
    {
        $category = new ModerationCategory('violence', true, 0.95);

        $this->assertEquals('violence', $category->category);
        $this->assertTrue($category->flagged);
        $this->assertEquals(0.95, $category->score);
    }
}
