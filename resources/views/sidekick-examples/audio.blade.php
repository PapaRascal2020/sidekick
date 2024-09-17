@extends('sidekick::sidekick-shared.layout')

@section('title')
    Audio Generation Sample
@endsection

@section('content')

    <!-- Chat Messages Area -->
    <div class="bg-slate-700 flex-1 p-6 overflow-y-auto">
        <div class="space-y-4">
            <div class="flex items-start justify-center">
                <div class="text-gray-300 w-3/4 text-center">
                    <p class="font-bold text-3xl mb-20">&#129302; Audio Generation Sample</p>
                    <p>Enter text below and click <b class="font-bold">&#x23CE;</b> to generate an audio file.</p>

                    @if(isset($audio))
                        <div class="m-auto text-center w-1/3 pt-4">
                            <audio id="audioPlayer" controls>
                                <source src="data:audio/mpeg;base64,{!! $audio !!}" type="audio/mpeg" />
                            </audio>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <footer class="bg-slate-900 p-4">
        <form method="POST" action="/sidekick/playground/audio">
            <div class="flex">
                @csrf
                <input type="text" name="text_to_convert" class="flex-1 text-black border border-gray-300 rounded-md p-2 focus:outline-none focus:border-blue-600" placeholder="Type your message...">
                <button class="bg-blue-600 text-white px-4 py-2 ml-2 rounded-md hover:bg-blue-700">&#x23CE;</button>
            </div>
        </form>
    </footer>

@endsection
