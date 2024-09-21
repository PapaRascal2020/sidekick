<?php

use Illuminate\Http\Request;
use \PapaRascalDev\Sidekick\Sidekick;
use Illuminate\Support\Facades\Route;
use \PapaRascalDev\Sidekick\Drivers\OpenAi;
use PapaRascalDev\Sidekick\Models\Conversation;
use PapaRascalDev\Sidekick\SidekickConversation;

Route::post('/sidekick/playground/chat', function (Request $request) {
    // These are the settings from the drop down on the front end.
    $options = explode("|", $request->get('engine'));

    // This is the system prompt the user wants the AI to use
    $systemPrompt = $request->get('prompt');

    // Create a new instance of SideKick conv conversation
    $sidekick = new SidekickConversation();

    // Insert a new conversation into the database
    $conversation = $sidekick->begin(
        driver: new $options[0](),
        model: $options[1],
        systemPrompt: $systemPrompt
    );

    // Redirect the user to the main page for the conversation
    return view('Pages.chatroom', [
        'conversationId' => $conversation->conversation->id,
        'options' => $options[0],
        'conversations' => Conversation::all('id', 'model')
    ]);
});

Route::post('/sidekick/playground/chat/update', function (Request $request) {

    // Get the AI driver
    $engine = $request->get('engine');

    // Load a new instance of Sidekick Conversation
    $sidekick = new SidekickConversation(new $engine());

    // Resume the conversation
    $conversation = $sidekick->resume(
        conversationId: $request->get('conversation_id'),
    );

    // Send a new message
    return $conversation->sendMessage($request->get('message'), $request->get('stream'));

});

Route::get('/sidekick/playground/chat/{id}', function (string $id) {
    // Find the conversation in the database
    $conversation = Conversation::findOrFail($id);

    // Return the conversation to the browser
    return view('Pages.chatroom', [
        'conversationId' => $conversation->id,
        'options' => $conversation->class,
        'messages' => $conversation->messages
    ]);
});

Route::get('/sidekick/playground/chat/delete/{id}', function (string $id) {
    // Find the conversation in the database
    $conversation = Conversation::findOrFail($id);

    // Delete the conversation
    $conversation->delete();

    // Redirect to the main chat page.
    return redirect('/sidekick/playground/chat');
});

Route::get('/sidekick/playground/completion', function () {
    return view('Pages.completion');
});

Route::post('/sidekick/playground/completion', function (Request $request) {
    // Loads a new instance of Sidekick with OpenAI
    $sidekick = Sidekick::create(new OpenAi());

    // Send message
    $response = $sidekick->complete()->sendMessage(
        model: 'gpt-3.5-turbo',
        systemPrompt: 'You are a knowledge base, please answer there questions',
        message: $request->get('message')
    );

    // Return valid and invalid response to the front end.
    return $sidekick->validate($response) ? $sidekick->getResponse($response)
        : $sidekick->getErrorMessage($response);
});

Route::get('/sidekick/playground/audio', function () {
    return view('Pages.audio');
});

Route::post('/sidekick/playground/audio', function (Request $request) {
    // Loads a new instance of Sidekick with OpenAI
    $sidekick = Sidekick::create(new OpenAi());

    // Send text to be converted by Sidekick to audio
    $audio = $sidekick->audio()->fromText(
        model:'tts-1',
        text: $request->get('text_to_convert')
    );

    $savedFile = $sidekick->utilities()->store($audio, 'audio/mpeg');

    // Return the base64 encoded audio file to the front end
    return view('Pages.audio', ['audio' => base64_encode($audio), 'savedFile' => $savedFile]);
});

Route::post('/sidekick/playground/image', function (Request $request) {
    $sidekick = Sidekick::create(new OpenAi());
    $image =  $sidekick->image()->make(
        model:'dall-e-3',
        prompt: $request->get('text_to_convert'),
        width:'1024',
        height:'1024'
    );

    $savedFile = $sidekick->utilities()->store($image['data'][0]['url'], 'image/png');

    return view('Pages.image', ['image' => $image['data'][0]['url'], 'savedFile' => $savedFile]);
});

Route::post('/sidekick/playground/transcribe', function (Request $request) {
    $sidekick = Sidekick::create(new OpenAi());
    $response =  $sidekick->transcribe()->audioFile(
        model:'whisper-1',
        filePath:$request->get('audio')
    );
    return view('Pages.transcribe', ['response' => $response]);
});

Route::post('/sidekick/playground/embedding', function (Request $request) {
    $sidekick = Sidekick::create(new OpenAi());
    $response = $sidekick->embedding()->make(
        model:'text-embedding-3-large',
        input: $request->get('text'),
    );
    return view('Pages.embedding', ['response' => $response]);
});

Route::get('/sidekick/playground/moderate', function () {
    return view('Pages.moderate');
});

Route::post('/sidekick/playground/moderate', function (Request $request) {
    $sidekick = Sidekick::create(new OpenAi());
    $response = $sidekick->moderate()->text(
        model:'text-moderation-latest',
        content: $request->get('text')
    );
    return view('Pages.moderate', ['response' => $response]);
});

Route::get('/sidekick/playground/image', function () {
    return view('Pages.image');
});

Route::get('/sidekick/playground/transcribe', function () {
    return view('Pages.transcribe');
});

Route::get('/sidekick/playground/embedding', function () {
    return view('Pages.embedding');
});

Route::get('/sidekick/playground/chat', function () {
    return view('Pages.chat', 'conversations', $conversations);
});

Route::get('/sidekick/playground', function () {
    return view('Pages.index');
});

