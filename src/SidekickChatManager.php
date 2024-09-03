<?php

namespace PapaRascalDev\Sidekick;

use Illuminate\Database\Eloquent\Collection;
use PapaRascalDev\Sidekick\Models\Conversation;

class SidekickChatManager
{
    /**
     * @return Collection
     */
    public function showAll(): Collection
    {
        return Conversation::all();
    }

    /**
     * @param Conversation $conversation
     * @return array
     */
    public function show(Conversation $conversation): array
    {
        return [
            'conversation_id' => $conversation->id,
            'messages' => $conversation->messages()->get()->toArray()
        ];
    }

    /**
     * @param Conversation $conversationId
     * @return void
     */
    public function delete(Conversation $conversation): void
    {
        $conversation->delete();
    }
}
