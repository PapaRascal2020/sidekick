<?php

namespace App\Livewire\Sidekick;

use Livewire\Component;
use PapaRascalDev\Sidekick\Drivers\OpenAi;

class Completion extends Component
{
    public $errors = null;

    public $prompt = '';
    public $response = null;

    public function submit()
    {

        $this->response = sidekick(new OpenAi)->complete(
            model: 'gpt-3.5-turbo',
            systemPrompt: 'You are a knowledge base, please answer there questions',
            message: $this->prompt
        );


        $this->prompt = '';

    }

    public function render()
    {
        return view('livewire.completion')->layout('Components.layouts.app', [
            'title' => 'Completion',
            'conversations' => sidekickConversation()->database()->all('id', 'model')
        ]);
    }
}
