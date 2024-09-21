<?php

namespace PapaRascalDev\Sidekick\Tests\Feature;

use PapaRascalDev\Sidekick\Drivers\OpenAi;
use Illuminate\Foundation\Testing\TestCase;
use PapaRascalDev\Sidekick\Models\Conversation;
use PapaRascalDev\Sidekick\SidekickChatManager;
use PapaRascalDev\Sidekick\SidekickConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SidekickConversationTest extends TestCase
{
    use RefreshDatabase;

    protected SidekickConversation $conversationMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->conversationMock = $this->createMock(SidekickConversation::class);

        $this->conversationMock->method('begin')
            ->willReturnCallback(function ($driver, $model, $systemPrompt) {
                $this->conversationMock->conversation = new Conversation();
                $this->conversationMock->conversation->class = get_class($driver);
                $this->conversationMock->conversation->model = $model;
                $this->conversationMock->conversation->system_prompt = $systemPrompt;
                $this->conversationMock->conversation->max_tokens = 1024;
                $this->conversationMock->conversation->save();

                return $this->conversationMock;
            });

        $this->conversationMock->method('resume')
            ->willReturnCallback(function ($id) {
                $this->conversationMock->conversation = Conversation::find($id);
                return $this->conversationMock;
            });
    }

    public function test_you_can_create_a_new_conversation()
    {
        $systemPrompt = 'You are an assistant';

        $this->conversationMock->begin(
            driver: new OpenAi(),
            model: 'gpt-3.5-turbo',
            systemPrompt: $systemPrompt
        );

        $this->assertDatabaseHas('sidekick_conversations', [
            'id' => $this->conversationMock->conversation->id,
            'system_prompt' => $systemPrompt,
        ]);
    }

    public function test_you_can_remove_new_conversation()
    {
        $systemPrompt = "You are an assistant";

        $this->conversationMock->begin(
            driver: new OpenAi(),
            model: 'gpt-3.5-turbo',
            systemPrompt: $systemPrompt
        );

        $this->assertDatabaseHas(Conversation::class, [
            'id' => $this->conversationMock->conversation->id,
            'system_prompt' => $systemPrompt,
        ]);

        $sidekickManager = new SidekickChatManager();
        $sidekickManager->delete($this->conversationMock->conversation);

        $this->assertDatabaseMissing(Conversation::class, ['id' => $this->conversationMock->conversation->id]);
    }

    public function test_you_can_resume_a_conversation()
    {
        $systemPrompt = "You are an assistant";

        $this->conversationMock->begin(
            driver: new OpenAi(),
            model: 'gpt-3.5-turbo',
            systemPrompt: $systemPrompt
        );

        $id = $this->conversationMock->conversation->id;

        $this->assertDatabaseHas(Conversation::class, [
            'id' => $id,
            'system_prompt' => $systemPrompt,
        ]);

        $this->conversationMock->resume($id);

        $this->assertSame($this->conversationMock->conversation->id, $id);
    }

    public function test_you_can_send_and_receive_a_message()
    {
        $systemPrompt = "You are an assistant";

        $this->conversationMock->begin(
            driver: new OpenAi(),
            model: 'gpt-3.5-turbo',
            systemPrompt: $systemPrompt
        );

        $this->assertDatabaseHas('sidekick_conversations', [
            'id' => $this->conversationMock->conversation->id,
            'system_prompt' => $systemPrompt,
        ]);

        $this->conversationMock->method('sendMessage')
            ->willReturnCallback(function ($message) {
                $wholeMessage = "Response to: " . $message; // Simulate a response message

                $this->conversationMock->conversation->messages()->create([
                    'role' => 'user',
                    'content' => $message
                ]);

                $this->conversationMock->conversation->messages()->create([
                    'role' => 'assistant',
                    'content' => nl2br($wholeMessage)
                ]);

                return $this->conversationMock;
            });

        $this->conversationMock->method('messages')
            ->willReturnCallback(function () {
                return $this->conversationMock->conversation->messages()->get()->toArray();
            });

        $this->conversationMock->sendMessage("Hello");

        // Assert that there are both the sent message and response
        $this->assertCount(2, $this->conversationMock->messages());
    }
}
