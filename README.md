
<p align="center">
    <a href="https://laravel.com"><img alt="Laravel Package" src="https://img.shields.io/badge/Laravel-10%2F11%2F12-red?logo=laravel&logoColor=white"/></a>&nbsp;&nbsp;&nbsp;
    <img alt="PHP" src="https://img.shields.io/badge/PHP-8.2%2B-blue?logo=php&logoColor=white"/> &nbsp;&nbsp;&nbsp;
    <img alt="Latest Version" src="https://img.shields.io/packagist/v/paparascaldev/sidekick?label=Latest"/> &nbsp;&nbsp;
    <a href="https://packagist.org/packages/paparascaldev/sidekick"><img alt="Packagist" src="https://img.shields.io/badge/Packagist-F28D1A?logo=Packagist&logoColor=white"/></a>
</p>

<p align="center">
<img src="sidekick.png" alt="Sidekick" width="200" />
</p>

<p align="center">
<a href="https://sidekickforlaravel.com"><img alt="Full Documentation" src="https://img.shields.io/badge/Full%20Documentation-sidekickforlaravel.com-ef4444?style=for-the-badge"/></a>
</p>

# Sidekick v2.0

A fluent Laravel package for integrating with **OpenAI**, **Anthropic Claude**, **Mistral**, and **Cohere** AI services. Features a modern builder API, typed responses, streaming support, database-backed conversations, an embeddable chat widget, and first-class testing support.

## Requirements

- PHP 8.2+
- Laravel 10, 11, or 12

## Installation

```bash
composer require paparascaldev/sidekick
php artisan sidekick:install
```

Or install manually:

```bash
composer require paparascaldev/sidekick
php artisan vendor:publish --tag=sidekick-config
php artisan migrate
```

## Configuration

Add your API keys to `.env` (only add the providers you use):

```dotenv
SIDEKICK_OPENAI_TOKEN=your-openai-key
SIDEKICK_CLAUDE_TOKEN=your-anthropic-key
SIDEKICK_MISTRAL_TOKEN=your-mistral-key
SIDEKICK_COHERE_TOKEN=your-cohere-key
```

Publish and customize the config file:

```bash
php artisan vendor:publish --tag=sidekick-config
```

### Config File Overview

The `config/sidekick.php` file includes:

```php
return [
    // Default provider when none specified
    'default' => env('SIDEKICK_DEFAULT_PROVIDER', 'openai'),

    // Default provider + model per capability
    'defaults' => [
        'text'          => ['provider' => 'openai', 'model' => 'gpt-4o'],
        'image'         => ['provider' => 'openai', 'model' => 'dall-e-3'],
        'audio'         => ['provider' => 'openai', 'model' => 'tts-1'],
        'transcription' => ['provider' => 'openai', 'model' => 'whisper-1'],
        'embedding'     => ['provider' => 'openai', 'model' => 'text-embedding-3-small'],
        'moderation'    => ['provider' => 'openai', 'model' => 'text-moderation-latest'],
    ],

    // Provider API keys and base URLs
    'providers' => [
        'openai'    => ['api_key' => env('SIDEKICK_OPENAI_TOKEN'), 'base_url' => '...'],
        'anthropic' => ['api_key' => env('SIDEKICK_CLAUDE_TOKEN'), 'base_url' => '...'],
        'mistral'   => ['api_key' => env('SIDEKICK_MISTRAL_TOKEN'), 'base_url' => '...'],
        'cohere'    => ['api_key' => env('SIDEKICK_COHERE_TOKEN'), 'base_url' => '...'],
    ],

    // Register custom providers
    'custom_providers' => [],

    // Chat widget settings
    'widget' => [
        'enabled'       => env('SIDEKICK_WIDGET_ENABLED', false),
        'route_prefix'  => 'sidekick',
        'middleware'     => ['web'],
        'provider'      => env('SIDEKICK_WIDGET_PROVIDER', 'openai'),
        'model'         => env('SIDEKICK_WIDGET_MODEL', 'gpt-4o'),
        'system_prompt' => env('SIDEKICK_WIDGET_SYSTEM_PROMPT', 'You are a helpful assistant.'),
        'max_tokens'    => 1024,
    ],

    // HTTP timeout and retry settings
    'http' => [
        'timeout'         => env('SIDEKICK_HTTP_TIMEOUT', 30),
        'connect_timeout' => env('SIDEKICK_HTTP_CONNECT_TIMEOUT', 10),
        'retry'           => ['times' => 0, 'sleep' => 100],
    ],
];
```

## Quick Start

You can use the `Sidekick` facade or the `sidekick()` helper function:

```php
use PapaRascalDev\Sidekick\Facades\Sidekick;

// Using the facade
$response = Sidekick::text()->withPrompt('Hello')->generate();

// Using the helper function
$response = sidekick()->text()->withPrompt('Hello')->generate();
```

### Text Generation

```php
$response = Sidekick::text()
    ->using('openai', 'gpt-4o')
    ->withSystemPrompt('You are a helpful assistant.')
    ->withPrompt('What is Laravel?')
    ->generate();

echo $response->text;                // "Laravel is a PHP web framework..."
echo $response->usage->totalTokens;  // 150
echo $response->meta->latencyMs;     // 523.4
echo $response->finishReason;        // "stop"
echo (string) $response;             // Same as $response->text
```

**All TextBuilder methods:**

| Method | Description |
|--------|-------------|
| `using(string $provider, ?string $model)` | Set provider and model |
| `withPrompt(string $prompt)` | Add a user message |
| `withSystemPrompt(string $prompt)` | Set the system prompt |
| `withMessages(array $messages)` | Set the full message history (array of `Message` objects or arrays) |
| `addMessage(Role $role, string $content)` | Append a single message with a specific role |
| `withMaxTokens(int $maxTokens)` | Set max tokens (default: 1024) |
| `withTemperature(float $temp)` | Set temperature (default: 1.0) |
| `generate(): TextResponse` | Execute and return a `TextResponse` |
| `stream(): StreamResponse` | Execute and return a streamable `StreamResponse` |

### Streaming

```php
$stream = Sidekick::text()
    ->using('anthropic', 'claude-sonnet-4-20250514')
    ->withPrompt('Write a haiku about coding')
    ->stream();

// Iterate over chunks
foreach ($stream as $chunk) {
    echo $chunk;
}

// Get the full buffered text after iteration
$fullText = $stream->text();

// Or return as an SSE response from a controller
return $stream->toResponse();
```

### Conversations (with DB persistence)

```php
// Start a conversation
$convo = Sidekick::conversation()
    ->using('openai', 'gpt-4o')
    ->withSystemPrompt('You are a travel advisor.')
    ->withMaxTokens(2048)
    ->begin();

$response = $convo->send('I want to visit Japan.');
echo $response->text;

// Get the conversation ID to resume later
$conversationId = $convo->getConversation()->id;

// Resume later
$convo = Sidekick::conversation()->resume($conversationId);
$response = $convo->send('What about accommodation?');

// Delete a conversation
$convo->delete();
```

**All ConversationBuilder methods:**

| Method | Description |
|--------|-------------|
| `using(string $provider, ?string $model)` | Set provider and model |
| `withSystemPrompt(string $prompt)` | Set the system prompt |
| `withMaxTokens(int $maxTokens)` | Set max tokens (default: 1024) |
| `begin(): self` | Start a new conversation (persisted to DB) |
| `resume(string $id): self` | Resume an existing conversation by UUID |
| `send(string $message): TextResponse` | Send a message and get a response |
| `delete(): bool` | Delete the conversation and its messages |
| `getConversation(): ?Conversation` | Get the underlying Eloquent model |

### Image Generation

```php
$response = Sidekick::image()
    ->using('openai', 'dall-e-3')
    ->withPrompt('A sunset over mountains')
    ->withSize('1024x1024')
    ->withQuality('hd')
    ->count(2)
    ->generate();

echo $response->url();           // First image URL
echo $response->urls;            // Array of all URLs
echo $response->revisedPrompt;   // DALL-E's revised prompt (if any)
```

**All ImageBuilder methods:**

| Method | Description |
|--------|-------------|
| `using(string $provider, ?string $model)` | Set provider and model |
| `withPrompt(string $prompt)` | Set the image prompt |
| `withSize(string $size)` | Set dimensions (default: `1024x1024`) |
| `withQuality(string $quality)` | Set quality: `standard` or `hd` (default: `standard`) |
| `count(int $count)` | Number of images to generate (default: 1) |
| `generate(): ImageResponse` | Execute and return an `ImageResponse` |

### Audio (Text-to-Speech)

```php
$response = Sidekick::audio()
    ->using('openai', 'tts-1')
    ->withText('Hello, welcome to Sidekick!')
    ->withVoice('nova')
    ->withFormat('mp3')
    ->generate();

$response->save('audio/welcome.mp3');       // Save to default disk
$response->save('audio/welcome.mp3', 's3'); // Save to specific disk
echo $response->format;                     // "mp3"
```

**All AudioBuilder methods:**

| Method | Description |
|--------|-------------|
| `using(string $provider, ?string $model)` | Set provider and model |
| `withText(string $text)` | Set the text to speak |
| `withVoice(string $voice)` | Set the voice (default: `alloy`) |
| `withFormat(string $format)` | Set audio format (default: `mp3`) |
| `generate(): AudioResponse` | Execute and return an `AudioResponse` |

### Transcription

```php
$response = Sidekick::transcription()
    ->using('openai', 'whisper-1')
    ->withFile('/path/to/audio.mp3')
    ->withLanguage('en')
    ->generate();

echo $response->text;       // Transcribed text
echo $response->language;   // "en"
echo $response->duration;   // Duration in seconds
echo (string) $response;    // Same as $response->text
```

**All TranscriptionBuilder methods:**

| Method | Description |
|--------|-------------|
| `using(string $provider, ?string $model)` | Set provider and model |
| `withFile(string $filePath)` | Path to the audio file |
| `withLanguage(string $language)` | Hint the language (optional) |
| `generate(): TranscriptionResponse` | Execute and return a `TranscriptionResponse` |

### Embeddings

```php
$response = Sidekick::embedding()
    ->using('openai', 'text-embedding-3-small')
    ->withInput('Laravel is a great framework')
    ->generate();

$vector = $response->vector();      // First embedding vector (array of floats)
$all = $response->embeddings;       // All embedding vectors
echo $response->usage->totalTokens; // Token usage
```

**All EmbeddingBuilder methods:**

| Method | Description |
|--------|-------------|
| `using(string $provider, ?string $model)` | Set provider and model |
| `withInput(string\|array $input)` | Text or array of texts to embed |
| `generate(): EmbeddingResponse` | Execute and return an `EmbeddingResponse` |

### Moderation

```php
$response = Sidekick::moderation()
    ->using('openai', 'text-moderation-latest')
    ->withContent('Some text to moderate')
    ->generate();

if ($response->isFlagged()) {
    // Content was flagged
}

if ($response->isFlaggedFor('violence')) {
    // Specifically flagged for violence
}

// Inspect all categories
foreach ($response->categories as $category) {
    echo "{$category->category}: flagged={$category->flagged}, score={$category->score}\n";
}
```

**All ModerationBuilder methods:**

| Method | Description |
|--------|-------------|
| `using(string $provider, ?string $model)` | Set provider and model |
| `withContent(string $content)` | Text to moderate |
| `generate(): ModerationResponse` | Execute and return a `ModerationResponse` |

### Utility Methods

Convenience methods that use the default text provider:

```php
// Summarize text (returns string)
$summary = Sidekick::summarize('Long text here...', maxLength: 500);

// Translate text (returns string)
$translated = Sidekick::translate('Hello', 'French');

// Extract keywords (returns string, comma-separated)
$keywords = Sidekick::extractKeywords('Some article text...');
```

## Knowledge Base / RAG

Sidekick includes a built-in RAG (Retrieval-Augmented Generation) system that lets you store business knowledge and ground AI responses in real data — preventing hallucinations about return policies, pricing, support hours, etc.

### Setup

RAG works out of the box with the default config. The `knowledge` section in `config/sidekick.php` controls embedding provider, chunk sizes, and search settings:

```php
'knowledge' => [
    'embedding' => ['provider' => 'openai', 'model' => 'text-embedding-3-small'],
    'chunking'  => ['chunk_size' => 2000, 'overlap' => 200],
    'search'    => ['default_limit' => 5, 'min_score' => 0.3, 'driver' => VectorSearch::class],
],
```

### Ingesting Content

```php
use PapaRascalDev\Sidekick\Facades\Sidekick;

// Ingest text
Sidekick::knowledge('my-kb')
    ->ingest('Our return policy is 30 days with receipt.', 'faq');

// Ingest a file
Sidekick::knowledge('my-kb')
    ->ingestFile('/path/to/faq.md');

// Ingest multiple texts
Sidekick::knowledge('my-kb')
    ->ingestMany(['Text one...', 'Text two...'], 'bulk-source');

// Use a different embedding provider
Sidekick::knowledge('my-kb')
    ->using('mistral', 'mistral-embed')
    ->ingest('Content here...');
```

Or use the Artisan command:

```bash
# Ingest a file
php artisan sidekick:ingest my-kb --file=/path/to/faq.md

# Ingest inline text
php artisan sidekick:ingest my-kb --text="Return policy is 30 days."

# Ingest a directory of .txt/.md/.html/.csv files
php artisan sidekick:ingest my-kb --dir=/path/to/docs

# Purge and re-ingest
php artisan sidekick:ingest my-kb --purge --dir=/path/to/docs
```

### Searching

```php
// Search for relevant chunks
$results = Sidekick::knowledge('my-kb')->search('What is your return policy?');

foreach ($results as $chunk) {
    echo $chunk->content;       // The text content
    echo $chunk->similarity;    // Cosine similarity score
    echo $chunk->source;        // Source label
}
```

### Ask (Search + Generate)

```php
// One-liner: search KB and generate a grounded answer
$answer = Sidekick::knowledge('my-kb')->ask('What is your return policy?');
```

### Widget Integration

Connect a knowledge base to the chat widget so it answers from your data:

```dotenv
SIDEKICK_WIDGET_ENABLED=true
SIDEKICK_WIDGET_KNOWLEDGE_BASE=my-kb
```

Or in `config/sidekick.php`:

```php
'widget' => [
    'knowledge_base'    => 'my-kb',
    'rag_context_chunks' => 5,
    'rag_min_score'      => 0.3,
],
```

When configured, the widget will automatically search the knowledge base for each user message and inject relevant context into the system prompt. If RAG fails (API error, empty KB), it falls back gracefully to the original system prompt.

### Custom Search Drivers

The default `VectorSearch` driver computes cosine similarity in PHP. For production workloads, you can swap in a custom driver (e.g., pgvector, Pinecone):

```php
// Implement the SearchesKnowledge contract
use PapaRascalDev\Sidekick\Contracts\SearchesKnowledge;

class PgVectorSearch implements SearchesKnowledge
{
    public function search(KnowledgeBase $kb, array $queryEmbedding, int $limit = 5, float $minScore = 0.3): Collection
    {
        // Your pgvector implementation
    }
}

// Register in config/sidekick.php
'knowledge' => [
    'search' => ['driver' => \App\Search\PgVectorSearch::class],
],
```

### Managing Knowledge Bases

```php
$kb = Sidekick::knowledge('my-kb');

$kb->chunkCount();           // Number of chunks stored
$kb->purge();                // Delete all chunks
$kb->getKnowledgeBase();     // Get the Eloquent model
```

## Chat Widget

Sidekick ships with an Alpine.js-powered chat widget you can embed in any Blade template. No Livewire required.

### Enable the widget

In your `.env`:

```dotenv
SIDEKICK_WIDGET_ENABLED=true
SIDEKICK_WIDGET_PROVIDER=openai
SIDEKICK_WIDGET_MODEL=gpt-4o
SIDEKICK_WIDGET_SYSTEM_PROMPT="You are a helpful assistant."
```

### Add to a Blade template

```blade
<x-sidekick::chat-widget
    position="bottom-right"
    theme="dark"
    title="AI Assistant"
    placeholder="Ask me anything..."
    button-label="Chat"
/>
```

**Props:**

| Prop | Default | Options |
|------|---------|---------|
| `position` | `bottom-right` | `bottom-right`, `bottom-left`, `top-right`, `top-left` |
| `theme` | `light` | `light`, `dark` |
| `title` | `Chat Assistant` | Any string |
| `placeholder` | `Type a message...` | Any string |
| `button-label` | `Chat` | Any string |

Make sure your layout includes Alpine.js and a CSRF meta tag:

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

## Custom Providers

Register custom providers at runtime or via config:

```php
// Runtime registration
Sidekick::registerProvider('ollama', function ($app) {
    return new OllamaProvider(config('sidekick.providers.ollama'));
});

// Or in config/sidekick.php
'custom_providers' => [
    'ollama' => \App\Sidekick\OllamaProvider::class,
],
```

Custom providers should implement `ProviderContract` and the relevant capability interfaces (`ProvidesText`, `ProvidesImages`, `ProvidesAudio`, `ProvidesTranscription`, `ProvidesEmbeddings`, `ProvidesModeration`).

## Testing

Sidekick provides first-class testing support with `Sidekick::fake()`:

```php
use PapaRascalDev\Sidekick\Facades\Sidekick;
use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

public function test_my_feature(): void
{
    $fake = Sidekick::fake([
        new TextResponse(
            text: 'Mocked response',
            usage: new Usage(10, 20, 30),
            meta: new Meta('openai', 'gpt-4o'),
        ),
    ]);

    // ... run your code that uses Sidekick ...

    $fake->assertTextGenerated();       // At least one text generation
    $fake->assertTextGenerated(2);      // Exactly 2 text generations
    $fake->assertNothingSent();         // No API calls were made
    $fake->assertProviderUsed('openai');
    $fake->assertModelUsed('gpt-4o');
    $fake->assertPromptContains('expected text');
}
```

## Events

Sidekick dispatches Laravel events you can listen to:

| Event | Properties | When |
|-------|-----------|------|
| `RequestSending` | `provider`, `model`, `capability` | Before a request is sent |
| `ResponseReceived` | `provider`, `model`, `capability`, `response` | After a successful response |
| `StreamChunkReceived` | `provider`, `model`, `chunk` | For each streaming chunk |
| `RequestFailed` | `provider`, `model`, `capability`, `exception` | When a request fails |

All events are in the `PapaRascalDev\Sidekick\Events` namespace.

## Provider Capabilities

| Capability | OpenAI | Anthropic | Mistral | Cohere |
|-----------|--------|-----------|---------|--------|
| Text | Yes | Yes | Yes | Yes |
| Image | Yes | - | - | - |
| Audio (TTS) | Yes | - | - | - |
| Transcription | Yes | - | - | - |
| Embedding | Yes | - | Yes | - |
| Moderation | Yes | - | - | - |

## API Key Resources

- [OpenAI](https://platform.openai.com)
- [Anthropic](https://console.anthropic.com)
- [Mistral](https://console.mistral.ai)
- [Cohere](https://dashboard.cohere.com)

## Contributing

Contributions are welcome! Whether it's bug reports, feature requests, documentation improvements, or code — we'd love your help.

- **Report a bug or request a feature**: [Open an issue](https://github.com/PapaRascal2020/sidekick/issues)
- **Submit code**: Fork the repo, create a branch, and [open a pull request](https://github.com/PapaRascal2020/sidekick/pulls)
- **Questions or ideas**: [Start a discussion](https://github.com/PapaRascal2020/sidekick/discussions)

See [CONTRIBUTING.md](CONTRIBUTING.md) for the full guide (forking, branching, PR process, coding standards).

## License

GPL-2.0-or-later. See [LICENSE](LICENSE) for details.

## Stargazers

[![Stargazers repo roster for @PapaRascal2020/sidekick](https://reporoster.com/stars/dark/notext/PapaRascal2020/sidekick)](https://github.com/PapaRascal2020/sidekick/stargazers)
