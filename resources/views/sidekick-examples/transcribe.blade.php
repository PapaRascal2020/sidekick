@extends('sidekick::sidekick-shared.layout')

@section('title')
    Transcription Generation Sample
@endsection

@section('content')

    <!-- Chat Messages Area -->
    <div class="bg-slate-700 flex-1 p-6 overflow-y-auto">
        <div class="space-y-4">
            <div class="flex items-start justify-center">
                <div class="text-gray-300 w-3/4 text-center">
                    <p class="font-bold text-3xl mb-20">&#129302; Transcription Generation Sample</p>
                    <p>Enter a URL to an audio file and click <b class="font-bold">convert</b> to generate the transcription (audio to text).
                        For convenience, Sidekick has added an example URL in case you don't know where to find one.</p>
                </div>
            </div>

            <div class="flex items-start justify-center">
                @if(isset($response))
                    <div class="bg-gray-200 p-4 mt-20 rounded-lg w-3/4">
                        <p class="text-gray-800 font-bold">Response</p>
                        <p class="text-gray-800">{!! $response['text'] !!}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <footer class="bg-slate-900 p-4">
        <form method="POST" action="/sidekick/playground/transcribe">
            <div class="flex">
                @csrf
                <input type="text" name="audio" class="flex-1 text-black border border-gray-300 rounded-md p-2 focus:outline-none focus:border-blue-600" placeholder="" value="http://english.voiceoversamples.com/ENG_UK_M_PeterB.mp3">
                <button class="bg-blue-600 text-white px-4 py-2 ml-2 rounded-md hover:bg-blue-700">Convert</button>
            </div>
        </form>
    </footer>

@endsection

