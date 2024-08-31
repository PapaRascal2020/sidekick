<?php

/*
 * Package Configuration
 */
return [
    'config' => [
        'driver' => [
            'OpenAi' => [
                'baseUrl' => 'https://api.openai.com/v1/',
                'headers' => [
                    'Authorization' => 'Bearer ' . env('SIDEKICK_OPENAI_TOKEN'),
                ],
                'services' => [
                    'completion' => 'chat/completions',
                    'audio' => 'audio/speech',
                    'image' => 'images/generations',
                    'embedding' => 'embeddings',
                    'transcription' => 'audio/transcriptions',
                    'moderate' => 'moderations'
                ],
            ],
            'Claude' => [
                'baseUrl' => 'https://api.anthropic.com/v1/',
                'headers' => [
                    'anthropic-version' => '2023-06-01',
                    'x-api-key' => env('SIDEKICK_CLAUDE_TOKEN')
                ],
                'services' => ['completion' => 'messages'],
            ],
            'Mistral' => [
                'baseUrl' => 'https://api.mistral.ai/v1/',
                'headers' => [
                    'Authorization' => 'Bearer ' . env('SIDEKICK_MISTRAL_TOKEN'),
                ],
                'services' => [
                    'completion' => 'chat/completions',
                    'embedding' => 'embeddings',
                ],
            ],
        ],
    ]
];
