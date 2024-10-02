<?php

namespace App\Livewire\Sidekick;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Collection;

class ChatRoom extends Component
{
    public $conversationId;
    public $config;
    public Collection $messages;
    public $newMessage;
    public $isStreaming = false;
    public $streamingResponse = '';

    public function mount($id = null)
    {
        $this->messages = collect();
        if ($id) {
            $this->loadConversation($id);
        }
    }

    public function loadConversation($id)
    {
        $conversation = sidekickConversation()->resume($id);
        $this->conversationId = $conversation->model->id;
        $this->config = [
            'engine' => $conversation->model->class,
            'model' => $conversation->model->model,
        ];
        $this->messages = collect($conversation->model->messages)->map(function ($message) {
            return (object) $message;
        });
    }

    public function sendMessage()
    {
        $this->messages->push((object) [
            'role' => 'user',
            'content' => $this->newMessage
        ]);

        if ($this->isStreaming) {
            $this->streamingResponse = '';
            $this->dispatch('startStreaming');
        } else {
            $response = sidekickConversation()
                ->resume($this->conversationId)
                ->sendMessage($this->newMessage, false);

            $this->messages->push((object) [
                'role' => 'assistant',
                'content' => $response
            ]);
        }

        $this->newMessage = '';
        $this->dispatch('messageAdded');
    }

    #[On('streamChunk')]
    public function handleStreamChunk($chunk)
    {
        $this->streamingResponse .= $chunk;
    }

    #[On('endStreaming')]
    public function endStreaming()
    {
        $this->messages->push((object) [
            'role' => 'assistant',
            'content' => $this->streamingResponse
        ]);
        $this->streamingResponse = '';
    }

    public function delete($id)
    {
        sidekickConversation()->delete($id);
        return redirect()->to('/sidekick/chat');
    }

    public function render()
    {
        return view('livewire.chat-room')->layout('Components.layouts.app', [
            'title' => 'Chat',
            'conversations' => sidekickConversation()->database()->all('id', 'model')
        ]);
    }
}
