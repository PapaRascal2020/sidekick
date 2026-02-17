<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    |
    | The default AI provider to use when none is explicitly specified.
    |
    */

    'default' => env('SIDEKICK_DEFAULT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Default Models Per Capability
    |--------------------------------------------------------------------------
    |
    | Default model to use for each capability when not specified.
    |
    */

    'defaults' => [
        'text' => [
            'provider' => env('SIDEKICK_DEFAULT_PROVIDER', 'openai'),
            'model' => env('SIDEKICK_DEFAULT_TEXT_MODEL', 'gpt-4o'),
        ],
        'image' => [
            'provider' => 'openai',
            'model' => 'dall-e-3',
        ],
        'audio' => [
            'provider' => 'openai',
            'model' => 'tts-1',
        ],
        'transcription' => [
            'provider' => 'openai',
            'model' => 'whisper-1',
        ],
        'embedding' => [
            'provider' => 'openai',
            'model' => 'text-embedding-3-small',
        ],
        'moderation' => [
            'provider' => 'openai',
            'model' => 'text-moderation-latest',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    |
    | Configuration for each AI provider including API keys and base URLs.
    |
    */

    'providers' => [

        'openai' => [
            'api_key' => env('SIDEKICK_OPENAI_TOKEN'),
            'base_url' => env('SIDEKICK_OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        ],

        'anthropic' => [
            'api_key' => env('SIDEKICK_CLAUDE_TOKEN'),
            'base_url' => env('SIDEKICK_ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
            'api_version' => '2023-06-01',
        ],

        'mistral' => [
            'api_key' => env('SIDEKICK_MISTRAL_TOKEN'),
            'base_url' => env('SIDEKICK_MISTRAL_BASE_URL', 'https://api.mistral.ai/v1'),
        ],

        'cohere' => [
            'api_key' => env('SIDEKICK_COHERE_TOKEN'),
            'base_url' => env('SIDEKICK_COHERE_BASE_URL', 'https://api.cohere.com/v2'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Providers
    |--------------------------------------------------------------------------
    |
    | Register custom providers here. Each entry should be a key (provider name)
    | mapped to a class or closure that returns a ProviderContract instance.
    |
    | Example:
    |   'ollama' => \App\Sidekick\OllamaProvider::class,
    |
    */

    'custom_providers' => [],

    /*
    |--------------------------------------------------------------------------
    | Knowledge Base / RAG
    |--------------------------------------------------------------------------
    |
    | Settings for the retrieval-augmented generation (RAG) knowledge base.
    |
    */

    'knowledge' => [
        'embedding' => [
            'provider' => 'openai',
            'model' => 'text-embedding-3-small',
        ],
        'chunking' => [
            'chunk_size' => 2000,
            'overlap' => 200,
        ],
        'search' => [
            'default_limit' => 5,
            'min_score' => 0.3,
            'driver' => \PapaRascalDev\Sidekick\Knowledge\VectorSearch::class,
        ],
        'rag_prompt_template' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Chat Widget
    |--------------------------------------------------------------------------
    |
    | Settings for the embeddable Alpine.js chat widget.
    |
    */

    'widget' => [
        'enabled' => env('SIDEKICK_WIDGET_ENABLED', false),
        'route_prefix' => 'sidekick',
        'middleware' => ['web'],
        'provider' => env('SIDEKICK_WIDGET_PROVIDER', 'openai'),
        'model' => env('SIDEKICK_WIDGET_MODEL', 'gpt-4o'),
        'system_prompt' => env('SIDEKICK_WIDGET_SYSTEM_PROMPT', 'You are a helpful assistant.'),
        'max_tokens' => 1024,
        'knowledge_base' => env('SIDEKICK_WIDGET_KNOWLEDGE_BASE', null),
        'rag_context_chunks' => 5,
        'rag_min_score' => 0.3,
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Settings
    |--------------------------------------------------------------------------
    |
    | Default HTTP settings for provider requests.
    |
    */

    'http' => [
        'timeout' => env('SIDEKICK_HTTP_TIMEOUT', 30),
        'connect_timeout' => env('SIDEKICK_HTTP_CONNECT_TIMEOUT', 10),
        'retry' => [
            'times' => 0,
            'sleep' => 100,
        ],
    ],

];
