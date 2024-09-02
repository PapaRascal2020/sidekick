@extends('sidekick::sidekick-shared.layout')

@section('title')
    Talk to Sidekick!
@endsection

@section('content')

    <!-- Chat Messages Area -->
    <div class="bg-slate-700 flex-1 p-6 overflow-y-auto">
        <div class="space-y-4">
            <div class="flex items-start justify-center">
                <div class="text-gray-300 w-3/4 text-center">
                    <p class="font-bold text-3xl mb-20">&#129302; Assistant</p>
                    <p>Hi! I am Laravel Sidekick, Just start typing below to start up a conversation with me.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <footer class="bg-slate-900 p-4">
            <form method="POST" action="/sidekick/playground/chat">
                <div class="flex">
                    @csrf
                    <input type="text" name="message" class="flex-1 text-black border border-gray-300 rounded-md p-2 focus:outline-none focus:border-blue-600" placeholder="Type your message...">
                    <button class="bg-blue-600 text-white px-4 py-2 ml-2 rounded-md hover:bg-blue-700">Send</button>
                </div>
            </form>
    </footer>
@endsection

