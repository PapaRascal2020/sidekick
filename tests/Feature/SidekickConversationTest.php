<?php

namespace PapaRascalDev\Sidekick\Tests\Feature;

use Illuminate\Http\JsonResponse;
use PapaRascalDev\Sidekick\Drivers\OpenAi;
use Illuminate\Foundation\Testing\TestCase;
use PapaRascalDev\Sidekick\SidekickConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PapaRascalDev\Sidekick\Models\SidekickConversation as SidekickConversationModel;

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
                $this->conversationMock->model = new SidekickConversationModel();
                $this->conversationMock->model->class = get_class($driver);
                $this->conversationMock->model->model = $model;
                $this->conversationMock->model->system_prompt = $systemPrompt;
                $this->conversationMock->model->max_tokens = 1024;
                $this->conversationMock->model->save();

                return $this->conversationMock;
            });

        $this->conversationMock->method('resume')
            ->willReturnCallback(function ($id) {
                $this->conversationMock->model = SidekickConversationModel::find($id);
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
            'id' => $this->conversationMock->model->id,
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

        $this->assertDatabaseHas(
            table: SidekickConversationModel::class,
            data: [
                'id' => $this->conversationMock->model->id,
                'system_prompt' => $systemPrompt,
                ]
        );

        $conversation = new SidekickConversation();
        $conversation->delete( $this->conversationMock->model->id );

        $this->assertDatabaseMissing(SidekickConversationModel::class,
            ['id' => $this->conversationMock->model->id] );
    }

    public function test_you_can_resume_a_conversation()
    {
        $systemPrompt = "You are an assistant";

        $this->conversationMock->begin(
            driver: new OpenAi(),
            model: 'gpt-3.5-turbo',
            systemPrompt: $systemPrompt
        );

        $id = $this->conversationMock->model->id;

        $this->assertDatabaseHas(SidekickConversationModel::class, [
            'id' => $id,
            'system_prompt' => $systemPrompt,
        ]);

        $this->conversationMock->resume($id);

        $this->assertSame($this->conversationMock->model->id, $id);
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
            'id' => $this->conversationMock->model->id,
            'system_prompt' => $systemPrompt,
        ]);

        $this->conversationMock->method('sendMessage')
            ->willReturnCallback(function ($message) {
                $wholeMessage = "Response to: " . $message; // Simulate a response message

                $this->conversationMock->model->messages()->create([
                    'role' => 'user',
                    'content' => $message
                ]);

                $this->conversationMock->model->messages()->create([
                    'role' => 'assistant',
                    'content' => nl2br($wholeMessage)
                ]);

                return new JsonResponse(['message' => $wholeMessage]);
            });

        $this->conversationMock->sendMessage("Hello");

        $databaseRec = (new SidekickConversation())->database()->find($this->conversationMock->model->id);

        // Assert that there are both the sent message and response
        $this->assertCount(2, $databaseRec->messages->toArray());
    }
}
