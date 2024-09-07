<?php

namespace PapaRascalDev\Sidekick;

use Illuminate\Support\Arr;
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

        if($this->sidekick->listAsObject) {
            $allMessages = $this->toCustomArray($this->messages(), $this->sidekick->chatMaps);
        } else {
            $allMessages = [
                ...$this->toCustomArray($this->messages(), $this->sidekick->chatMaps),
            ];
        }

        $response = $this->sidekick->complete()->sendMessage(
            model: $this->conversation->model,
            systemPrompt: $this->conversation->system_prompt,
            allMessages: $allMessages,
            message: $message,
            maxTokens: $this->conversation->max_tokens);

        if($this->sidekick->validate($response)) {
            $this->conversation->messages()->create([
                'role' => $this->sidekick->messageRoles['user'],
                'content' => $message
            ]);

            $this->conversation->messages()->create([
                'role' => $this->sidekick->messageRoles['assistant'],
                'content' => $this->sidekick->getResponse($response)
            ]);

            return [
                'conversation_id' => $this->conversation->id,
                'messages' => $this->conversation->messages()->get()->toArray(),
            ];
        }

        return $this->sidekick->getErrorMessage($response);

    }
    public function toCustomArray(
        array $messages,
        array $mappings = [],
    ): array
    {
        $mappedMessages = [];
        foreach($messages as $message) {
            foreach ($mappings as $oldKey => $newKey) {
                $message[$newKey] = $message[$oldKey];
                unset($message[$oldKey]);
            }
            $mappedMessages[] = $message;
        }

        return $mappedMessages;
    }

    /**
     * @return void
     */
    public function messages(): array
    {
        return $this->conversation->messages()->get()->toArray();
    }
}
