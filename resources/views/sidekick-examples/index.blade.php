@extends('sidekick::sidekick-shared.layout')

@section('title')
    Welcome to Sidekick Playground
@endsection

@section('content')

    <!-- Chat Messages Area -->
    <div class="bg-slate-700 flex-1 p-6 overflow-y-auto">
        <div class="space-y-4">
            <div class="flex items-start justify-center">
                <div class="text-gray-300 w-3/4 text-center">
                    <h1 class="font-bold text-3xl">Hello!</h1>
                    <img src="https://substackcdn.com/image/fetch/w_1456,c_limit,f_webp,q_auto:good,fl_progressive:steep/https%3A%2F%2Fsubstack-post-media.s3.amazonaws.com%2Fpublic%2Fimages%2F0e3449a7-44de-4ce2-b384-cea763c0901e_500x500.heic" class="pt-20" alt="Sidekick Robot" />

                    <p class="pt-20"><strong class="font-bold">Note:</strong> To use this playground you must have configured the .env with your <i>SIDEKICK_OPENAI_TOKEN</i>.</p>
                </div>
            </div>
        </div>
    </div>

@endsection
