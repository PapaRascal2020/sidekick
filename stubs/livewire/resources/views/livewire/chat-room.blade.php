<div
    x-data="{
        startStreaming() {
            this.streamResponse();
        },
        async streamResponse() {
            const response = await fetch('/sidekick/chat/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    message: @js($this->newMessage),
                    conversation_id: @js($this->conversationId),
                    stream: true
                })
            });

            const reader = response.body.getReader();
            const decoder = new TextDecoder();

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;
                const chunk = decoder.decode(value, { stream: true });
                @this.dispatch('streamChunk', chunk);
            }

            @this.dispatch('endStreaming');
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const scrollableDiv = this.$refs.scrollableDiv;
                scrollableDiv.scrollTop = scrollableDiv.scrollHeight;
            });
        },
    }"
    x-init="scrollToBottom()"
    @start-streaming.window="startStreaming"
    @message-added.window="$wire.$refresh().then(() => scrollToBottom())"
    class="h-full flex flex-col"
>
    <!-- Header -->
    <header class="bg-slate-900 shadow p-4 flex justify-between items-center text-white">
        <h1 class="text-xl text-white font-semibold">Conversation <small class="text-sm">(id: {{$conversationId}}) -  Model: {{ $config['model'] ?? 'Auto-Select'}}</small></h1>

        <div>
            <a href="/sidekick/chat" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">New Chat</a>
            <button wire:click="delete('{{$conversationId}}')" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">X</button>
        </div>
    </header>

    <!-- Chat Messages Area -->
    <div id="scrollableDiv" x-ref="scrollableDiv" class="bg-slate-700 flex-1 p-6 overflow-y-auto">
        <div id="response-container" class="space-y-4">
            @if($messages->isEmpty())
                <div class="bg-slate-700 text-center">
                    <p class="text-gray-400">To start the conversation send a message</p>
                </div>
            @else
                @foreach($messages as $message)
                    @if(strtolower($message->role) === 'user')
                        <x-sidekick-user-message>
                            {!! $message->content !!}
                        </x-sidekick-user-message>
                    @else
                        <x-sidekick-bot-message r="{{ $message->id ?? '' }}">
                            {!! nl2br($message->content) !!}
                        </x-sidekick-bot-message>
                    @endif
                @endforeach
            @endif

            @if($streamingResponse)
                <div class="bg-slate-600 p-4 rounded-lg mb-4">
                    <p class="text-white">{{ $streamingResponse }}</p>
                </div>
            @endif
        </div>
    </div>

    <footer class="bg-slate-900 p-4">
        <form wire:submit.prevent="sendMessage">
            <div class="flex items-center justify-between gap-4">
                <label class="inline-flex items-center cursor-pointer mt-2">
                    <input wire:model="isStreaming" type="checkbox" class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    <span class="ms-3 text-sm font-medium text-gray-900 pr-4 dark:text-gray-300">Stream</span>
                </label>
                <input wire:model="newMessage" type="text" class="flex-1 rounded-l-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-600 text-black" placeholder="Type your message...">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700">&#x23CE;</button>
            </div>
        </form>
    </footer>
</div>
