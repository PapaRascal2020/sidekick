<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PapaRascalDev\Sidekick\Models\Conversation;
use \PapaRascalDev\Sidekick\Sidekick;
use \PapaRascalDev\Sidekick\Drivers\OpenAi;
use PapaRascalDev\Sidekick\SidekickConversation;

Route::get('/sidekick/playground', function () {
    return view('sidekick::sidekick-examples.index');
});

Route::post('/sidekick/playground/chat', function (Request $request) {
    $options = explode("|", $request->get('engine'));
    $systemPrompt = $request->get('prompt');
    $sidekick = new SidekickConversation();

    $conversation = $sidekick->begin(
        driver: new $options[0](),
        model: $options[1],
        systemPrompt: $systemPrompt
    );

    return view('sidekick::sidekick-examples.chatroom', [
        'conversationId' => $conversation->conversation->id,
        'options' => $options[0]
    ]);
});

Route::post('/sidekick/playground/chat/update', function (Request $request) {
    $engine = $request->get('engine');
    $sidekick = new SidekickConversation(new $engine());

    $conversation = $sidekick->resume(
        conversationId: $request->get('conversation_id')
    );

    $response = $conversation->sendMessage($request->get('message'));

    return response()->json([
        'response' => $response,
        'options' => $engine
    ]);
});

Route::get('/sidekick/playground/chat', function () {
    return view('sidekick::sidekick-examples.chat');
});

Route::get('/sidekick/playground/chat/{id}', function (string $id) {
    $conversation = Conversation::findOrFail($id);
    return view('sidekick::sidekick-examples.chatroom', [
        'conversationId' => $conversation->id,
        'options' => $conversation->model,
        'messages' => $conversation->messages
    ]);
});

Route::get('/sidekick/playground/chat/delete/{id}', function (string $id) {
    $conversation = Conversation::findOrFail($id);
    $conversation->delete();
    return redirect('/sidekick/playground/chat');
});

Route::get('/sidekick/playground/completion', function () {
    return view('sidekick::sidekick-examples.completion');
});

Route::post('/sidekick/playground/completion', function (Request $request) {
    $sidekick = Sidekick::create(new OpenAi());
    $response = $sidekick->complete()->sendMessage(
        model: 'gpt-3.5-turbo',
        systemPrompt: 'You are a knowledge base, please answer there questions',
        message: $request->get('message')
    );

    return $sidekick->validate($response) ? $sidekick->getResponse($response)
        : $sidekick->getErrorMessage($response);
});

Route::get('/sidekick/playground/audio', function () {
    return view('sidekick::sidekick-examples.audio');
});

Route::post('/sidekick/playground/audio', function (Request $request) {
    $sidekick = Sidekick::create(new OpenAi());

    $audio = $sidekick->audio()->fromText(
        model:'tts-1',
        text: $request->get('text_to_convert')
    );

    return view('sidekick::sidekick-examples.audio', ['audio' => base64_encode($audio)]);
});

Route::post('/sidekick/playground/image', function (Request $request) {
    $sidekick = Sidekick::create(new OpenAi());
    $image =  $sidekick->image()->make(
        model:'dall-e-3',
        prompt: $request->get('text_to_convert'),
        width:'1024',
        height:'1024'
    );

    return view('sidekick::sidekick-examples.image', ['image' => $image['data'][0]['url']]);
});

Route::get('/sidekick/playground/image', function () {
    return view('sidekick::sidekick-examples.image');
});

Route::get('/sidekick/playground/transcribe', function () {
    return view('sidekick::sidekick-examples.transcribe');
});

Route::post('/sidekick/playground/transcribe', function (Request $request) {
    $sidekick = Sidekick::create(new OpenAi());
    $response =  $sidekick->transcribe()->audioFile(
        model:'whisper-1',
        filePath:$request->get('audio')
    );
    return view('sidekick::sidekick-examples.transcribe', ['response' => $response]);
});

Route::get('/sidekick/playground/embedding', function () {
    return view('sidekick::sidekick-examples.embedding');
});

Route::post('/sidekick/playground/embedding', function (Request $request) {
    $sidekick = Sidekick::create(new OpenAi());
    $response = $sidekick->embedding()->make(
        model:'text-embedding-3-large',
        input: $request->get('text'),
    );
    return view('sidekick::sidekick-examples.embedding', ['response' => $response]);
});

Route::get('/sidekick/playground/moderate', function () {
    return view('sidekick::sidekick-examples.moderate');
});

Route::post('/sidekick/playground/moderate', function (Request $request) {
    $sidekick = Sidekick::create(new OpenAi());
    $response = $sidekick->moderate()->text(
        model:'text-moderation-latest',
        content: $request->get('text')
    );
    return view('sidekick::sidekick-examples.moderate', ['response' => $response]);
});
