@extends('Shared.layout')

@section('title')
    Image Generation Sample
@endsection

@section('content')

    <!-- Chat Messages Area -->
    <div class="bg-slate-700 flex-1 p-6 overflow-y-auto">
        <div class="space-y-4">
            <div class="flex items-start justify-center">
                <div class="text-gray-300 w-3/4 text-center">
                    <p class="font-bold text-3xl mb-20">&#129302; Image Generation Sample</p>
                    <p>Describe the image you want in the text box below and click <b class="font-bold">&#x23CE;</b> to generate an image.</p>
                    @if(isset($image))
                        <img src="{!! $image !!}" alt="generated image" class="w-full pt-20"/>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <x-sidekick-form url="/sidekick/image" />

@endsection
