@extends('sidekick::Blade.Shared.layout')

@section('title')
    Embedding Generation Sample
@endsection

@section('content')

    <!-- Chat Messages Area -->
    <div class="bg-slate-700 flex-1 p-6 overflow-y-auto">
        <div class="space-y-4">
            <div class="flex items-start justify-center">
                <div class="text-gray-300 w-3/4 text-center">
                    <p class="font-bold text-3xl mb-20">&#129302; Embedding Generation Sample</p>
                    <p>Type some text into the box below and click <strong class="font-bold">&#x23CE;</strong> to see it's vector representation response.</p>
                </div>
            </div>

            <div class="flex items-start justify-center">
                @if(isset($response))
                    <div class="bg-gray-200 p-4 mt-20 rounded-lg w-3/4">
                        <p class="text-gray-800 font-bold">Response</p>
                        <textarea class="text-gray-800 bg-gray-200 w-full h-48">{{ json_encode($response['data'][0]['embedding']) }}</textarea>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <footer class="bg-slate-900 p-4">
        <form method="POST" action="/sidekick/playground/embedding">
            <div class="flex">
                @csrf
                <input type="text" name="text" class="flex-1 text-black border border-gray-300 rounded-md p-2 focus:outline-none focus:border-blue-600" placeholder="Type some text..." >
                <button class="bg-blue-600 text-white px-4 py-2 ml-2 rounded-md hover:bg-blue-700">&#x23CE;</button>
            </div>
        </form>
    </footer>

@endsection
