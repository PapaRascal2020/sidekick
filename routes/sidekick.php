<?php

use Illuminate\Support\Facades\Route;
use PapaRascalDev\Sidekick\Http\Controllers\ChatWidgetController;

Route::group([
    'prefix' => config('sidekick.widget.route_prefix', 'sidekick'),
    'middleware' => config('sidekick.widget.middleware', ['web']),
], function () {
    Route::post('/chat/message', [ChatWidgetController::class, 'message'])
        ->name('sidekick.chat.message');
});
