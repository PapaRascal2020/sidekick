<?php

namespace App\Livewire\Sidekick;

use Livewire\Component;
use PapaRascalDev\Sidekick\Drivers\OpenAi;

class Embeddding extends Component
{

    public $errors = null;

    public $prompt = '';
    public $response = null;

    public function submit()
    {

        $this->response = sidekick(new OpenAi)->embedding()->make(
            model:'text-embedding-3-large',
            input: $this->prompt,
        );


        $this->prompt = '';

    }

    public function render()
    {
        return view('livewire.embeddding')->layout('Components.layouts.app', [
            'title' => 'Embedding',
            'conversations' => sidekickConversation()->database()->all('id', 'model')
        ]);
    }
}
