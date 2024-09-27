<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \PapaRascalDev\Sidekick\Drivers\OpenAi;

Route::post('/sidekick/chat', function (Request $request) {

    // These are the settings from the drop down on the front end.
    $config = json_decode($request->get('config'));

    // This is the system prompt the user wants the AI to use
    $systemPrompt = $request->get('prompt');

    // Call sidekick and send the first message
    $conversation = sidekickConversation()->begin(
        driver: new $config->engine(),
        model: $config->model,
        systemPrompt: $systemPrompt
    );

    // Redirect the user to the main page for the conversation
    return view('Pages.chatroom', [
        'conversationId' => $conversation->model->id,
        'config' => $config,
        'conversations' => sidekickConversation()->database()->all('id', 'model')
    ]);
});

Route::post('/sidekick/chat/update', function (Request $request) {

    // Load the instance of Sidekick Conversations and sendMessage
    return sidekickConversation()
            ->resume( $request->get('conversation_id') )
            ->sendMessage($request->get('message'), $request->get('stream'));
});

Route::get('/sidekick/chat/{id}', function (string $id) {
    // load the conversation
    $conversation = sidekickConversation()->resume($id);

    // Return the conversation to the browser
    return view('Pages.chatroom', [
        'conversationId' => $conversation->model->id,
        'config' => [
            'model' => $conversation->model->class,
        ],
        'messages' => $conversation->model->messages,
        'conversations' => sidekickConversation()->database()->all('id', 'model')
    ]);
});

Route::get('/sidekick/chat/delete/{id}', function (string $id) {
    // Find and delete the conversation in the database
    sidekickConversation()->delete($id);

    // Redirect to the main chat page.
    return redirect('/sidekick/chat');
});

Route::get('/sidekick/completion', function () {
    return view('Pages.completion', ['conversations' => sidekickConversation()->database()->all('id', 'model')]);
});

Route::post('/sidekick/completion', function (Request $request) {

    // Send message for a response
    return sidekick(new OpenAi)->complete(
        model: 'gpt-3.5-turbo',
        systemPrompt: 'You are a knowledge base, please answer there questions',
        message: $request->get('prompt')
    );
});

Route::get('/sidekick/audio', function () {
    return view('Pages.audio', ['conversations' => sidekickConversation()->database()->all('id', 'model')]);
});

Route::post('/sidekick/audio', function (Request $request) {

    // Send text to be converted by Sidekick to audio
    $audio = sidekick(new OpenAi)->audio()->fromText(
        model:'tts-1',
        text: $request->get('prompt')
    );

    $savedFile = sidekick(new OpenAi)->utilities()->store($audio, 'audio/mpeg');

    // Return the base64 encoded audio file to the front end
    return view('Pages.audio', [
        'audio' => base64_encode($audio),
        'savedFile' => $savedFile,
        'conversations' => sidekickConversation()->database()->all('id', 'model')
    ]);
});

Route::post('/sidekick/image', function (Request $request) {
    $image =  sidekick(new OpenAi)->image()->make(
        model:'dall-e-3',
        prompt: $request->get('prompt'),
        width:'1024',
        height:'1024'
    );

    $savedFile = sidekick(new OpenAi)->utilities()->store($image['data'][0]['url'], 'image/png');

    return view('Pages.image', [
        'image' => $image['data'][0]['url'],
        'savedFile' => $savedFile,
        'conversations' => sidekickConversation()->database()->all('id', 'model')
    ]);
});

Route::post('/sidekick/transcribe', function (Request $request) {
    $response =  sidekick(new OpenAi)->transcribe()->audioFile(
        model:'whisper-1',
        filePath:$request->get('prompt')
    );
    return view('Pages.transcribe', [
        'response' => $response,
        'conversations' => sidekickConversation()->database()->all('id', 'model')
    ]);
});

Route::post('/sidekick/embedding', function (Request $request) {
    $response = sidekick(new OpenAi)->embedding()->make(
        model:'text-embedding-3-large',
        input: $request->get('prompt'),
    );
    return view('Pages.embedding', [
        'response' => $response,
        'conversations' => sidekickConversation()->database()->all('id', 'model')
    ]);
});

Route::get('/sidekick/moderate', function () {
    return view('Pages.moderate', ['conversations' => sidekickConversation()->database()->all('id', 'model')]);
});

Route::post('/sidekick/moderate', function (Request $request) {
    $response = sidekick(new OpenAi)->moderate()->text(
        model:'text-moderation-latest',
        content: $request->get('prompt')
    );
    return view('Pages.moderate', [
        'response' => $response,
        'conversations' => sidekickConversation()->database()->all('id', 'model')
    ]);
});

Route::get('/sidekick/image', function () {
    return view('Pages.image', ['conversations' => sidekickConversation()->database()->all('id', 'model')]);
});

Route::get('/sidekick/transcribe', function () {
    return view('Pages.transcribe', ['conversations' => sidekickConversation()->database()->all('id', 'model')]);
});

Route::get('/sidekick/embedding', function () {
    return view('Pages.embedding', ['conversations' => sidekickConversation()->database()->all('id', 'model')]);
});

Route::get('/sidekick/chat', function () {
    return view('Pages.chat', ['conversations' => sidekickConversation()->database()->all('id', 'model')]);
});

Route::get('/sidekick', function () {
    return view('Pages.index', ['conversations' => sidekickConversation()->database()->all('id', 'model')]);
});

