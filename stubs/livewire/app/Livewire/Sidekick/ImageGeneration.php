<?php

namespace App\Livewire\Sidekick;

use Livewire\Component;
use PapaRascalDev\Sidekick\Drivers\OpenAi;

class ImageGeneration extends Component
{

    public $prompt = '';
    public $image = null;
    public $savedFile = null;

    public $errors = null;

    public $model = 'dall-e-3';

    public function submit()
    {
        $response = sidekick(new OpenAi)->image()->make(
            model:$this->model,
            prompt: $this->prompt,
            width:'1024',
            height:'1024'
        );

        $this->prompt = '';

        if(isset($response['data'][0]['url'])) {
            $this->image = $response['data'][0]['url'];
            $this->savedFile = sidekick(new OpenAi)->utilities()->store($response['data'][0]['url'], 'image/png');
        } else {
            $this->errors = $response['error']['message'];
        }
    }

    public function render()
    {
        return view('livewire.image-generation')
            ->layout('Components.layouts.app', [
                    'title' => 'Image Generation',
                    'conversations' => sidekickConversation()->database()->all('id', 'model')
                ]
            );
    }
}
