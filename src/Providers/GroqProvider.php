<?php

namespace PapaRascalDev\Sidekick\Providers;

use PapaRascalDev\Sidekick\Enums\Capability;

/**
 * Groq exposes an OpenAI-compatible API (the same /chat/completions endpoint,
 * Bearer auth, and json_schema structured output), so it reuses the OpenAI
 * provider wholesale and only changes its name. The Groq base URL and token are
 * set under config('sidekick.providers.groq').
 */
class GroqProvider extends OpenAiProvider
{
    public function name(): string
    {
        return 'groq';
    }

    public function capabilities(): array
    {
        // Groq serves chat completions (and Whisper transcription); we expose text.
        return [Capability::Text];
    }
}
