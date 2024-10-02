<?php

namespace App\Livewire\Sidekick;

use Livewire\Component;
use PapaRascalDev\Sidekick\Drivers\OpenAi;

class AudioGeneration extends Component
{
    public $errors = null;

    public $prompt = '';
    public $audio = null;
    public $savedFile = null;

    public $model = 'tts-1';

    public function submit()
    {
        $response = sidekick(new OpenAi)->audio()->fromText(
            model: 'tts-1',
            text: $this->prompt);

        $response_json = json_decode($response, true);

        if(isset($response_json['error'])) {
            $this->errors = $response_json['error']['message'];
        } else {
            $this->audio = base64_encode($response);
            $this->savedFile = sidekick(new OpenAi)->utilities()->store($response, 'audio/mpeg');
        }
    }

    public function render()
    {
        return view('livewire.audio-generation')
            ->layout('Components.layouts.app', [
                    'title' => 'Audio Generation',
                    'conversations' => sidekickConversation()->database()->all('id', 'model')
                ]
            );
    }
}
