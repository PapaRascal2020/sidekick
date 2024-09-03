<?php

namespace PapaRascalDev\Sidekick;

use PapaRascalDev\Sidekick\Drivers\Driver;
use PapaRascalDev\Sidekick\Models\Conversation;

class SidekickConversation
{
    protected Driver $sidekick;
    protected Conversation $conversation;

    /**
     * @param Driver $driver
     */
    function __construct(Driver $driver)
    {
        $this->sidekick = Sidekick::create($driver);
    }

    /**
     * @param string $conversationId
     * @return $this
     */
    public function resume(string $conversationId): static
    {
        $this->conversation = Conversation::findOrFail($conversationId);
        return $this;
    }

    /**
     * @param string $model
     * @param string $systemPrompt
     * @param int $maxTokens
     * @return $this
     */
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

    /**
     * @param string $message
     * @return array
     */
    public function sendMessage(string $message) {
        $newMessage = ['role' => 'user', 'content' => $message];
        $allMessages = [
            ...$this->conversation->messages()->get()->toArray(),
            $newMessage
        ];

        $response = $this->sidekick->complete()->sendMessage(
            model: $this->conversation->model,
            systemPrompt: $this->conversation->system_prompt,
            messages: $allMessages,
            maxTokens: $this->conversation->max_tokens);

        if($this->sidekick->validate($response)) {
            $this->conversation->messages()->create($newMessage);

            $chatResponse = [
                'role' => 'assistant',
                'content' => $this->sidekick->getResponse($response)
            ];

            $this->conversation->messages()->create($chatResponse);

            return [
                'conversation_id' => $this->conversation->id,
                'messages' => $this->conversation->messages()->get()->toArray()
            ];
        }

        return $this->sidekick->getErrorMessage($response);

    }

}
