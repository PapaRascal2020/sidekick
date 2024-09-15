@extends('sidekick::sidekick-shared.layout')

@section('title')
    Talk to Sidekick! - Chat
@endsection

@section('content')
    <!-- Header -->
    <header class="bg-slate-900 shadow p-4 flex justify-between items-center">
        <h1 class="text-xl text-white font-semibold text-gray-900">Conversation <small class="text-sm">(id: {{$conversationId}}) - Engine: {{($options != '') ? $options : 'Auto-Select'}}</small></h1>

        <div>
            <a href="/sidekick/playground/chat" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">New Chat</a>
            <a href="/sidekick/playground/chat/delete/{{$conversationId}}" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">X</a>
        </div>
    </header>

    <!-- Chat Messages Area -->
    <div id="scrollableDiv" class="bg-slate-700 flex-1 p-6 overflow-y-auto">
        <div id="response-container" class="space-y-4">
            <div class="bg-slate-700 text-center">
                <p class="text-gray-400">To start the conversation send a message</p>
            </div>
            @if(isset($messages))
                @foreach($messages as $message)
                    @if(strtolower($message['role']) === 'user')
                        <div class="flex items-start">
                            <div class="bg-gray-200 p-4 rounded-lg w-3/4">
                                <p class="text-gray-800 font-bold">&#128583; User</p>
                                <p class="text-gray-800">{{ $message['content'] }}</p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-start justify-end">
                            <div class="bg-blue-600 text-white p-4 rounded-lg w-3/4">
                                <p class="font-bold">&#129302; Assistant</p>
                                <p>{{ $message['content'] }}</p>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

        </div>
    </div>

    <!-- Input Area -->
    <footer class="bg-slate-900 p-4">
        <form method="POST" id="completion-form">
            <div class="flex">
                <input id="conversation_id" type="hidden" name="conversation_id" value="{{$conversationId}}" />
                <input id="engine" type="hidden" name="engine" value="{{$options}}" />
                <input id="message-input" type="text" name="message"  class="flex-1 border border-gray-300 text-black rounded-md p-2 focus:outline-none focus:border-blue-600" placeholder="Type your message...">
                <input type="submit" class="bg-blue-600 text-white px-4 py-2 ml-2 rounded-md hover:bg-blue-700" value="Send">
            </div>
        </form>
    </footer>
@endsection

@prepend('page-scripts')
    <script type="text/javascript">

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('completion-form');
            const container = document.getElementById('scrollableDiv');
            const messageInput = document.getElementById('message-input');
            const engine = document.getElementById('engine');
            const conversationId = document.getElementById('conversation_id');
            const responseContainer = document.getElementById('response-container');

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();
                event.stopPropagation();

                const message = messageInput.value;

                messageInput.value = "";

                responseContainer.innerHTML += `
                    <div class="flex items-start">
                        <div class="bg-gray-200 p-4 rounded-lg w-3/4">
                            <p class="text-gray-800 font-bold">&#128583; User</p>
                            <p class="text-gray-800">${message}</p>
                        </div>
                    </div>

                    <div id="loader" class="flex items-center justify-center bg-slate-700 text-center text-gray-400 p-4">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Generating response...
                    </div>
                `;

                container.scrollTop = container.scrollHeight;

                fetch('/sidekick/playground/chat/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ message: message, conversation_id: conversationId.value, engine: engine.value })
                })
                    .then(response => response.json())
                    .then(data => {
                        const loader = document.getElementById('loader');
                        const response = data.response.messages[data.response.messages.length - 1];

                        loader.remove();

                        responseContainer.innerHTML += `<div class="flex items-start justify-end">
                            <div class="bg-blue-600 text-white p-4 rounded-lg w-3/4">
                                <p class="font-bold">&#129302; Assistant</p>
                                <p>${response.content}</p>
                            </div>
                        </div>`;

                        container.scrollTop = container.scrollHeight;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        //responseText.textContent = 'An error occurred while processing the request.';
                        //responseContainer.style.display = 'block';
                    });
            });
        });
    </script>
@endprepend
