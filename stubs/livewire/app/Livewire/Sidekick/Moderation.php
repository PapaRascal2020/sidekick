<?php

namespace App\Livewire\Sidekick;

use Livewire\Component;
use PapaRascalDev\Sidekick\Drivers\OpenAi;

class Moderation extends Component
{
    public $response = null;
    public $prompt = '';

    public function submit()
    {
        $this->response = sidekick(new OpenAi)->moderate()->text(
            model:'text-moderation-latest',
            content: $this->prompt
        );
    }

    public function render()
    {
        return view('livewire.moderation')->layout('Components.layouts.app', [
            'title' => 'Moderation',
            'conversations' => sidekickConversation()->database()->all('id', 'model')
        ]);
    }
}
