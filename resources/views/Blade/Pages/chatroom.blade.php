@extends('sidekick::Blade.Shared.layout')

@section('title')
    Talk to Sidekick! - Chat
@endsection

@section('content')
    <!-- Header -->
    <header class="bg-slate-900 shadow p-4 flex justify-between items-center text-white">
        <h1 class="text-xl text-white font-semibold">Conversation <small class="text-sm">(id: {{$conversationId}}) - Engine: {{($options != '') ? $options : 'Auto-Select'}}</small></h1>

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
                                <p class="font-bold flex items-center gap-x-1 pb-2 text-black">
                                    @include('sidekick::Blade.Components.user') User</p>
                                <p class="text-gray-800">{!! $message['content'] !!}</p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-start justify-end">
                            <div class="bg-blue-800 text-white p-4 rounded-lg w-3/4">
                                <p class="font-bold flex items-center gap-x-1 pb-2">
                                    @include('sidekick::Blade.Components.bot') Assistant</p>
                                <p>{!! $message['content'] !!}</p>
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
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="stream" name="stream" value="" class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    <span class="ms-3 text-sm font-medium text-gray-900 pr-4 dark:text-gray-300">Stream</span>
                </label>
                <input id="message-input" type="text" name="message" required class="flex-1 border border-gray-300 text-black rounded-md p-2 focus:outline-none focus:border-blue-600" placeholder="Type your message...">
                <input type="submit" class="bg-blue-600 text-white px-4 py-2 ml-2 rounded-md hover:bg-blue-700" value="&#x23CE;">
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
            const stream = document.getElementById('stream');
            const conversationId = document.getElementById('conversation_id');
            const responseContainer = document.getElementById('response-container');

            container.scrollTop = container.scrollHeight;

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();
                event.stopPropagation();

                const message = messageInput.value;

                messageInput.value = "";
                form.disabled = true;


                let isStreamed = stream.checked;

                responseContainer.innerHTML += `
                    <div class="flex items-start">
                        <div class="bg-gray-200 p-4 rounded-lg w-3/4">
                            <p class="font-bold flex items-center gap-x-1 pb-2 text-black">
                                    @include('sidekick::Blade.Components.user') User</p>
                            <p class="text-gray-800">${message}</p>
                        </div>
                    </div>
                `;

                responseContainer.innerHTML += `
                    <div id="loader" class="flex items-center justify-center bg-slate-700 text-center text-gray-400 p-4">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Generating response...
                    </div>
                `;

                container.scrollTop = container.scrollHeight;

                if (isStreamed) {
                    handleStreamedCallback();
                } else {
                    handleCallback()
                }

                async function handleStreamedCallback() {
                    // Set random ID for appending chunked data to Assistant Message
                    let r = (Math.random() + 1).toString(36).substring(7);

                    // Create response container
                    responseContainer.innerHTML += `
                    <div class="flex items-start justify-end">
                        <div class="bg-blue-800 text-white p-4 rounded-lg w-3/4">
                            <p class="font-bold flex items-center gap-x-1 pb-2">
                                @include('sidekick::Blade.Components.bot') Assistant</p>
                            <p id="response-${r}"></p>
                        </div>
                    </div>
                    `;

                    // Make fetch request to server
                    fetch('/sidekick/playground/chat/update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            message: message,
                            conversation_id: conversationId.value,
                            engine: engine.value,
                            stream: true
                        })
                    }).then(async (response) => {
                        const reader = response.body.getReader();
                        const decoder = new TextDecoder();

                        const responseBox = document.getElementById(`response-${r}`);

                        container.scrollTop = container.scrollHeight;

                        while (true) {
                            const {done, value} = await reader.read();

                            if(done) break;

                            const chunk = decoder.decode(value, {stream: true});

                            responseBox.innerText += chunk;
                            container.scrollTop = container.scrollHeight;
                        }

                        const loader = document.getElementById('loader');
                        loader.remove();

                    }).catch(error => {
                        const loader = document.getElementById('loader');
                        loader.remove();
                        console.error('Error:', error);
                    });
                }

                function handleCallback() {
                    fetch('/sidekick/playground/chat/update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            message: message,
                            conversation_id: conversationId.value,
                            engine: engine.value,
                            stream: false
                        })
                    }).then(
                        response => response.json()
                    ).then(data => {
                        const response = data.response.messages[data.response.messages.length - 1];

                        const loader = document.getElementById('loader');
                        loader.remove();

                        responseContainer.innerHTML += `
                        <div class="flex items-start justify-end">
                            <div class="bg-blue-800 text-white p-4 rounded-lg w-3/4">
                                <p class="font-bold flex items-center gap-x-1 pb-2">
                                        @include('sidekick::Blade.Components.bot') Assistant</p>
                                <p>${response.content}</p>
                            </div>
                        </div>
                        `;

                        container.scrollTop = container.scrollHeight;
                    }).catch(error => {
                        console.error('Error:', error);
                    });
                }
            })
        });
    </script>
@endprepend
