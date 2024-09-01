<?php

namespace PapaRascalDev\Sidekick;

use PapaRascalDev\Sidekick\Drivers\Driver;
use PapaRascalDev\Sidekick\Models\Conversation;

class SidekickConversation
{
    protected Driver $sidekick;
    protected Conversation $conversation;

    function __construct(Driver $driver)
    {
        $this->sidekick = Sidekick::create($driver);
    }

    public function resume(string $conversationId): static
    {
        $this->conversation = Conversation::findOrFail($conversationId);
        return $this;
    }

    public function begin(
        string $model,
        string $systemPrompt = '',
        int $maxTokens = 1024
    ): static
    {
        $this->conversation = new Conversation();
        $this->conversation->model = $model;
        $this->conversation->class = get_class($this->sidekick);
        $this->conversation->system_prompt = $systemPrompt;
        $this->conversation->max_tokens = $maxTokens;
        $this->conversation->save();

        return $this;
    }

    public function sendMessage(string $message) {
        $newMessage = ['role' => 'user', 'content' => $message];
        $allMessages = [
            ...$this->conversation->messages()->get(['role', 'content'])->toArray(),
            $newMessage
        ];

        $this->conversation->messages()->create($newMessage);

        $response = $this->sidekick->complete()->sendMessage(
            model: $this->conversation->model,
            systemPrompt: $this->conversation->system_prompt,
            messages: $allMessages,
            maxTokens: $this->conversation->max_tokens);

        $chatResponse = [
            'role' => 'assistant',
            'content' => $this->sidekick->uniformedResponse($response)
        ];

        $this->conversation->messages()->create($chatResponse);

        return [
            'conversation_id' => $this->conversation->id,
            'messages' => $this->conversation->messages()->get(['role', 'content'])->toArray()
        ];
    }

}
