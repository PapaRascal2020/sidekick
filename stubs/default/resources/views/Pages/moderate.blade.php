@extends('Shared.layout')

@section('title')
    Moderate Sample
@endsection

@section('content')

    <!-- Chat Messages Area -->
    <div class="bg-slate-700 flex-1 p-6 overflow-y-auto">
        <div class="space-y-4">
            <div class="flex items-start justify-center">
                <div class="text-gray-300 w-3/4 text-center">
                    <p class="font-bold text-3xl mb-20">&#129302; Moderate Sample</p>
                    <p>Type a dummy comment or post into the text box below and hit <strong class="font-bold">&#x23CE;</strong>. The AI will then review the content and send back a moderation response.</p>
                </div>
            </div>

            <div class="flex items-start justify-center">
                <div class="bg-gray-200 p-4 mt-20 rounded-lg w-3/4 text-black">
                    @if(isset($response))
                        @if (!$response['results'][0]['flagged'])
                            <p>The content is fine</p>
                        @else
                            <p>This content has been flagged due to the following categories:</p>
                            <ul>
                                @foreach($response['results'][0]['categories'] as $category => $value)
                                    @if($value)
                                        <li class="font-bold">{{ $category }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <footer class="bg-slate-900 p-4">
        <form method="POST" action="/sidekick/playground/moderate">
            <div class="flex">
                @csrf
                <input type="text" name="text" class="flex-1 text-black border border-gray-300 rounded-md p-2 focus:outline-none focus:border-blue-600" placeholder="Type some text..." >
                <button class="bg-blue-600 text-white px-4 py-2 ml-2 rounded-md hover:bg-blue-700">&#x23CE;</button>
            </div>
        </form>
    </footer>

@endsection

