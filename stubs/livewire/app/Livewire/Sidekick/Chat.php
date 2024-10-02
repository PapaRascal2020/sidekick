<?php

namespace App\Livewire\Sidekick;

use Livewire\Component;

class Chat extends Component
{

    public $prompt = '';
    public $config = '';


    public function submit()
    {

        $config = json_decode($this->config, true);

        if(!$config)
        {
            $config = [
                "engine" => "\PapaRascalDev\Sidekick\Drivers\OpenAi",
                "model" => "gpt-3.5-turbo"
            ];
        }

        // This is the system prompt the user wants the AI to use
        $systemPrompt = $this->prompt;


        $conversation = sidekickConversation()->begin(
            driver: new $config['engine'](),
            model: $config['model'],
            systemPrompt: $systemPrompt
        );

        return redirect()->to('/sidekick/chat/' . $conversation->id);
    }

    public function render()
    {
        return view('livewire.chat')->layout('Components.layouts.app', [
            'title' => 'Chat',
            'conversations' => sidekickConversation()->database()->all('id', 'model')
        ]);
    }
}
