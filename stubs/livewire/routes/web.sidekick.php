<?php

use App\Livewire\Sidekick\AudioGeneration;
use App\Livewire\Sidekick\Chat;
use App\Livewire\Sidekick\ChatRoom;
use App\Livewire\Sidekick\Completion;
use App\Livewire\Sidekick\Embeddding;
use App\Livewire\Sidekick\ImageGeneration;
use App\Livewire\Sidekick\Index;
use App\Livewire\Sidekick\Moderation;
use App\Livewire\Sidekick\Transcription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/sidekick', Index::class);
Route::get('/sidekick/chat', Chat::class);
Route::get('/sidekick/completion', Completion::class);
Route::get('/sidekick/audio', AudioGeneration::class);
Route::get('/sidekick/image', ImageGeneration::class);
Route::get('/sidekick/embedding', Embeddding::class);
Route::get('/sidekick/moderate', Moderation::class);
Route::get('/sidekick/transcribe', Transcription::class);

Route::post('/sidekick/chat/update', function (Request $request) {
    return sidekickConversation()
        ->resume($request->get('conversation_id'))
        ->sendMessage($request->get('message'), $request->get('stream'));
});

Route::get('/sidekick/chat/{id}', ChatRoom::class);


