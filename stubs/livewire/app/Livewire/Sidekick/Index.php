<?php

namespace App\Livewire\Sidekick;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.index')->layout('Components.layouts.app', [
            'title' => '',
            'conversations' => sidekickConversation()->database()->all('id', 'model')
        ]);
    }
}
