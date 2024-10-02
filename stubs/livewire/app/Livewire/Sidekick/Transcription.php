<?php

namespace App\Livewire\Sidekick;

use Livewire\Component;
use PapaRascalDev\Sidekick\Drivers\OpenAi;

class Transcription extends Component
{

    public $errors = null;

    public $prompt = 'http://english.voiceoversamples.com/ENG_UK_M_PeterB.mp3';
    public $response = null;

    public function submit()
    {

        $this->response =  sidekick(new OpenAi)->transcribe()->audioFile(
            model:'whisper-1',
            filePath:$this->prompt
        );

    }

    public function render()
    {
        return view('livewire.transcription')->layout('Components.layouts.app', [
            'title' => 'Transcription',
            'conversations' => sidekickConversation()->database()->all('id', 'model')
        ]);
    }
}
