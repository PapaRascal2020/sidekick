@extends('sidekick::sidekick-shared.layout')

@section('title')
    Talk to Sidekick! - Chat
@endsection

@section('content')
    <!-- Header -->
    <header class="bg-slate-900 shadow p-4 flex justify-between items-center">
        <h1 class="text-xl text-white font-semibold text-gray-900">Conversation (id: {{$response['conversation_id']}})</h1>
        <a href="/sidekick/playground/chat" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">New Chat</a>
    </header>

    <!-- Chat Messages Area -->
    <div id="scrollableDiv" class="bg-slate-700 flex-1 p-6 overflow-y-auto">
        <div class="space-y-4">
            @foreach($response['messages'] as $message)
                @if($message['role'] === 'user')
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
        </div>
    </div>

    <!-- Input Area -->
    <footer class="bg-slate-900 p-4">
        <form method="POST" action="/sidekick/playground/chat/update">
            <div class="flex">
                @csrf
                <input type="hidden" name="conversation_id" value="{{$response['conversation_id']}}" />
                <input type="text" name="message"  class="flex-1 border border-gray-300 text-black rounded-md p-2 focus:outline-none focus:border-blue-600" placeholder="Type your message...">
                <button class="bg-blue-600 text-white px-4 py-2 ml-2 rounded-md hover:bg-blue-700">Send</button>
            </div>
        </form>
    </footer>
@endsection

<script>
   function scrollToBottom() {
       const container = document.getElementById('scrollableDiv');
       container.scrollTop = container.scrollHeight;
   }

   // Scroll to bottom when the page loads
   window.onload = scrollToBottom;
</script>
