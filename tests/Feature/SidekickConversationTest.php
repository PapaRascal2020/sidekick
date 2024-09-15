<?php

namespace PapaRascalDev\Sidekick\Tests\Feature;

use Faker\Factory;
use Faker\Generator;
use PapaRascalDev\Sidekick\Drivers\OpenAi;
use Illuminate\Foundation\Testing\TestCase;
use PapaRascalDev\Sidekick\Models\Conversation;
use PapaRascalDev\Sidekick\SidekickChatManager;
use PapaRascalDev\Sidekick\SidekickConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SidekickConversationTest extends TestCase
{
    use RefreshDatabase;

    protected Generator $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function test_you_can_create_a_new_conversation()
    {
        $systemPrompt = $this->faker->sentence();

        $sidekick = new SidekickConversation();
        $sidekick->begin(
            driver: new OpenAi(),
            model: 'gpt-3.5-turbo',
            systemPrompt: $systemPrompt
        );

        $this->assertDatabaseHas(Conversation::class, [
            'id' => $sidekick->conversation->id,
            'system_prompt' => $systemPrompt,
        ]);
    }

    public function test_you_can_remove_new_conversation()
    {
        $systemPrompt = $this->faker->sentence();

        $sidekick = new SidekickConversation();
        $sidekick->begin(
            driver: new OpenAi(),
            model: 'gpt-3.5-turbo',
            systemPrompt: $systemPrompt
        );

        $this->assertDatabaseHas(Conversation::class, [
            'id' => $sidekick->conversation->id,
            'system_prompt' => $systemPrompt,
        ]);

        $sidekickManager = new SidekickChatManager();
        $sidekickManager->delete($sidekick->conversation);

        $this->assertDatabaseMissing(Conversation::class, ['id' => $sidekick->conversation->id]);
    }

    public function test_you_can_resume_a_conversation()
    {
        $systemPrompt = $this->faker->sentence();

        $sidekick = new SidekickConversation();
        $sidekick->begin(
            driver: new OpenAi(),
            model: 'gpt-3.5-turbo',
            systemPrompt: $systemPrompt
        );

        $id = $sidekick->conversation->id;

        $this->assertDatabaseHas(Conversation::class, [
            'id' => $id,
            'system_prompt' => $systemPrompt,
        ]);

        $sidekickConversation = new SidekickConversation();
        $sidekickConversation->resume($id);

        $this->assertSame($sidekickConversation->conversation->id, $id);
    }

    public function test_you_can_send_and_receive_a_message()
    {
        $systemPrompt = $this->faker->sentence();

        $sidekick = new SidekickConversation();
        $sidekick->begin(
            driver: new OpenAi(),
            model: 'gpt-3.5-turbo',
            systemPrompt: $systemPrompt
        );

        $this->assertDatabaseHas(Conversation::class, [
            'id' => $sidekick->conversation->id,
            'system_prompt' => $systemPrompt,
        ]);

        $sidekick->sendMessage("Hello");

        // Assert that there is both the sent message and response
        $this->assertCount(2, $sidekick->conversation->messages);
    }
}
