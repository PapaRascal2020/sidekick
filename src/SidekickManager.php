<?php

namespace PapaRascalDev\Sidekick;

use Illuminate\Database\Eloquent\Collection;
use PapaRascalDev\Sidekick\Models\Conversation;
class SidekickManager
{
    public function listConversations(): Collection
    {
        return Conversation::all();
    }

    public function showConversation(string $conversationId): array
    {
        $conversation =  Conversation::findOrFail($conversationId);

        return [
                'conversation_id' => $conversationId,
                'messages' => $conversation->messages()->get(['role', 'content'])->toArray()
            ];
    }

    public function deleteConversation(string $conversationId): void
    {
        $conversation = Conversation::findOrFail($conversationId);
        $conversation->delete();
    }
}
