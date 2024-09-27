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
    <x-sidekick-form url="/sidekick/playground/chat">
        <select name="engine" class="text-black">
            <option value="\PapaRascalDev\Sidekick\Drivers\OpenAi|gpt-3.5-turbo">Open AI : GPT 3.5 Turbo
            </option>
            <option value="\PapaRascalDev\Sidekick\Drivers\OpenAi|gpt-4">Open AI : GPT 4</option>
            @if(getenv('SIDEKICK_MISTRAL_TOKEN'))
                <option value="\PapaRascalDev\Sidekick\Drivers\Mistral|mistral-small-latest">Mistral : Small
                </option>
                <option value="\PapaRascalDev\Sidekick\Drivers\Mistral|mistral-medium-latest">Mistral : Medium
                </option>
                <option value="\PapaRascalDev\Sidekick\Drivers\Mistral|mistral-large-latest">Mistral : Large
                </option>
                <option value="\PapaRascalDev\Sidekick\Drivers\Mistral|open-mistral-7b">Mistral : Open Mistral
                    7B
                </option>
            @endif
            @if(getenv('SIDEKICK_CLAUDE_TOKEN'))
                <option value="\PapaRascalDev\Sidekick\Drivers\Claude|claude-3-opus-20240229">Claude : Opus
                </option>
                <option value="\PapaRascalDev\Sidekick\Drivers\Claude|claude-3-sonnet-20240229">Claude: Sonnet
                </option>
                <option value="\PapaRascalDev\Sidekick\Drivers\Claude|claude-3-haiku-20240307">Claude: Haiku
                </option>
            @endif
            @if(getenv('SIDEKICK_CLAUDE_TOKEN'))
                <option value="\PapaRascalDev\Sidekick\Drivers\Cohere|">Cohere : Auto-Select</option>
            @endif
        </select>
    </x-sidekick-form>
@endsection

