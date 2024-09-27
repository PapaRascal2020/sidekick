@extends('Shared.layout')

@section('title')
    Talk to Sidekick!
@endsection

@section('content')

    <!-- Chat Messages Area -->
    <div class="bg-slate-700 flex-1 p-6 overflow-y-auto">
        <div class="space-y-4">
            <div class="flex items-start justify-center">
                <div class="text-gray-300 w-3/4 text-center">
                    <p class="font-bold text-3xl mb-20">&#129302; Assistant</p>
                    <p>To begin a conversation select a model and type a system prompt to start.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Input form -->
    <x-sidekick-form url="/sidekick/chat">
        <select name="config" class="text-black">
            <option value='{"engine": "\\PapaRascalDev\\Sidekick\\Drivers\\OpenAi", "model": "gpt-3.5-turbo"}'>Open AI : GPT 3.5 Turbo</option>
            <option value='{"engine": "\\PapaRascalDev\\Sidekick\\Drivers\\OpenAi", "model": "gpt-4"}'>Open AI : GPT 4</option>
            @if(getenv('SIDEKICK_MISTRAL_TOKEN'))
                <option value='{"engine": "\\PapaRascalDev\\Sidekick\\Drivers\\\\Mistral", "model": "mistral-small-latest"}'>Mistral : Small</option>
                <option value='{"engine": "\\PapaRascalDev\\Sidekick\\Drivers\\\\Mistral", "model": "mistral-medium-latest"}'>Mistral : Medium</option>
                <option value='{"engine": "\\PapaRascalDev\\Sidekick\\Drivers\\\\Mistral", "model": "mistral-large-latest"}'>Mistral : Large</option>
                <option value='{"engine": "\\PapaRascalDev\\Sidekick\\Drivers\\\\Mistral", "model": "open-mistral-7b"}'>Mistral : Open Mistral 7B</option>
            @endif
            @if(getenv('SIDEKICK_CLAUDE_TOKEN'))
                <option value='{"engine": "\\PapaRascalDev\\Sidekick\\Drivers\\Claude", "model": "claude-3-opus-20240229"}'>Claude : Opus</option>
                <option value='{"engine": "\\PapaRascalDev\\Sidekick\\Drivers\\Claude", "model": "claude-3-sonnet-20240229"}'>Claude: Sonnet</option>
                <option value='{"engine": "\\PapaRascalDev\\Sidekick\\Drivers\\Claude", "model": "claude-3-haiku-20240307"}'>Claude: Haiku</option>
            @endif
            @if(getenv('SIDEKICK_CLAUDE_TOKEN'))
                <option value='{"engine": "\\PapaRascalDev\\Sidekick\\Drivers\\Cohere", "model": ""}'>Cohere : Auto-Select</option>
            @endif
        </select>
    </x-sidekick-form>
@endsection

