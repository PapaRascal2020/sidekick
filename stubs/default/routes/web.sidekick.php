<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \PapaRascalDev\Sidekick\Drivers\OpenAi;
use PapaRascalDev\Sidekick\Models\Conversation;
use PapaRascalDev\Sidekick\SidekickConversation;
use PapaRascalDev\Sidekick\Facades\Sidekick;

Route::post('/sidekick/playground/chat', function (Request $request) {

    // These are the settings from the drop down on the front end.
    $options = explode("|", $request->get('engine'));

    // This is the system prompt the user wants the AI to use
    $systemPrompt = $request->get('prompt');

    // Call sidekick and send the first message
    $conversation = sidekickConversation()->begin(
        driver: new $options[0](),
        model: $options[1],
        systemPrompt: $systemPrompt
    );

    // Redirect the user to the main page for the conversation
    return view('Pages.chatroom', [
        'conversationId' => $conversation->model->id,
        'options' => $options[0],
        'conversations' => sidekickConversation()->database()->all('id', 'model')
    ]);
});

Route::post('/sidekick/playground/chat/update', function (Request $request) {

    // Load the instance of Sidekick Conversations and sendMessage
    return sidekickConversation()
            ->resume( $request->get('conversation_id') )
            ->sendMessage($request->get('message'), $request->get('stream'));
});

Route::get('/sidekick/playground/chat/{id}', function (string $id) {
    // load the conversation
    $conversation = sidekickConversation()->resume($id);

    // Return the conversation to the browser
    return view('Pages.chatroom', [
        'conversationId' => $conversation->model->id,
        'options' => $conversation->model->class,
        'messages' => $conversation->model->messages
    ]);
});

Route::get('/sidekick/playground/chat/delete/{id}', function (string $id) {
    // Find and delete the conversation in the database
    sidekickConversation()->delete($id);

    // Redirect to the main chat page.
    return redirect('/sidekick/playground/chat');
});

Route::get('/sidekick/playground/completion', function () {
    return view('Pages.completion');
});

Route::post('/sidekick/playground/completion', function (Request $request) {

    // Send message for a response
    return sidekick(new OpenAi)->complete(
        model: 'gpt-3.5-turbo',
        systemPrompt: 'You are a knowledge base, please answer there questions',
        message: $request->get('message')
    );
});

Route::get('/sidekick/playground/audio', function () {
    return view('Pages.audio');
});

Route::post('/sidekick/playground/audio', function (Request $request) {

    // Send text to be converted by Sidekick to audio
    $audio = sidekick(new OpenAi)->audio()->fromText(
        model:'tts-1',
        text: $request->get('text_to_convert')
    );

    $savedFile = sidekick(new OpenAi)->utilities()->store($audio, 'audio/mpeg');

    // Return the base64 encoded audio file to the front end
    return view('Pages.audio', ['audio' => base64_encode($audio), 'savedFile' => $savedFile]);
});

Route::post('/sidekick/playground/image', function (Request $request) {
    $image =  sidekick(new OpenAi)->image()->make(
        model:'dall-e-3',
        prompt: $request->get('text_to_convert'),
        width:'1024',
        height:'1024'
    );

    $savedFile = sidekick(new OpenAi)->utilities()->store($image['data'][0]['url'], 'image/png');

    return view('Pages.image', ['image' => $image['data'][0]['url'], 'savedFile' => $savedFile]);
});

Route::post('/sidekick/playground/transcribe', function (Request $request) {
    $response =  sidekick(new OpenAi)->transcribe()->audioFile(
        model:'whisper-1',
        filePath:$request->get('audio')
    );
    return view('Pages.transcribe', ['response' => $response]);
});

Route::post('/sidekick/playground/embedding', function (Request $request) {
    $response = sidekick(new OpenAi)->embedding()->make(
        model:'text-embedding-3-large',
        input: $request->get('text'),
    );
    return view('Pages.embedding', ['response' => $response]);
});

Route::get('/sidekick/playground/moderate', function () {
    return view('Pages.moderate');
});

Route::post('/sidekick/playground/moderate', function (Request $request) {
    $response = sidekick(new OpenAi)->moderate()->text(
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
    return view('Pages.chat');
});

Route::get('/sidekick/playground', function () {
    return view('Pages.index');
});

