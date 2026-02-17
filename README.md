
<p align="center">
    <a href="https://laravel.com"><img alt="Laravel Package" src="https://img.shields.io/badge/Laravel-10%2F11%2F12-red?logo=laravel&logoColor=white"/></a>&nbsp;&nbsp;&nbsp;
    <img alt="PHP" src="https://img.shields.io/badge/PHP-8.2%2B-blue?logo=php&logoColor=white"/> &nbsp;&nbsp;&nbsp;
    <img alt="Latest Version" src="https://img.shields.io/packagist/v/paparascaldev/sidekick?label=Latest"/> &nbsp;&nbsp;
    <a href="https://packagist.org/packages/paparascaldev/sidekick"><img alt="Packagist" src="https://img.shields.io/badge/Packagist-F28D1A?logo=Packagist&logoColor=white"/></a>
</p>

<p align="center">
<img src="https://hopeful-mist.lon1.cdn.digitaloceanspaces.com/sidekick_new.png" alt="Sidekick" />
</p>

# Sidekick v2.0

A fluent Laravel package for integrating with **OpenAI**, **Anthropic Claude**, **Mistral**, and **Cohere** AI services. Features a modern builder API, typed responses, streaming support, database-backed conversations, an embeddable chat widget, and first-class testing support.

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

Add your API keys to `.env`:

```dotenv
SIDEKICK_OPENAI_TOKEN=your-openai-key
SIDEKICK_CLAUDE_TOKEN=your-anthropic-key
SIDEKICK_MISTRAL_TOKEN=your-mistral-key
SIDEKICK_COHERE_TOKEN=your-cohere-key
```

The config file (`config/sidekick.php`) lets you set default providers, models, HTTP timeouts, and widget settings.

## Quick Start

### Text Generation

```php
use PapaRascalDev\Sidekick\Facades\Sidekick;

$response = Sidekick::text()
    ->using('openai', 'gpt-4o')
    ->withSystemPrompt('You are a helpful assistant.')
    ->withPrompt('What is Laravel?')
    ->generate();

echo $response->text;           // "Laravel is a PHP web framework..."
echo $response->usage->totalTokens;  // 150
echo $response->meta->latencyMs;     // 523.4
```

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

// Or return as an SSE response from a controller
return $stream->toResponse();
```

### Conversations (with DB persistence)

```php
// Start a conversation
$convo = Sidekick::conversation()
    ->using('openai', 'gpt-4o')
    ->withSystemPrompt('You are a travel advisor.')
    ->begin();

$response = $convo->send('I want to visit Japan.');
echo $response->text;

// Resume later
$convo = Sidekick::conversation()->resume($conversationId);
$response = $convo->send('What about accommodation?');
```

### Image Generation

```php
$response = Sidekick::image()
    ->using('openai', 'dall-e-3')
    ->withPrompt('A sunset over mountains')
    ->withSize('1024x1024')
    ->generate();

echo $response->url(); // First image URL
```

### Audio (Text-to-Speech)

```php
$response = Sidekick::audio()
    ->using('openai', 'tts-1')
    ->withText('Hello, welcome to Sidekick!')
    ->withVoice('nova')
    ->generate();

$response->save('audio/welcome.mp3');
```

### Transcription

```php
$response = Sidekick::transcription()
    ->using('openai', 'whisper-1')
    ->withFile('/path/to/audio.mp3')
    ->generate();

echo $response->text;
```

### Embeddings

```php
$response = Sidekick::embedding()
    ->using('openai', 'text-embedding-3-small')
    ->withInput('Laravel is a great framework')
    ->generate();

$vector = $response->vector(); // First embedding vector
```

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
```

### Utility Methods

```php
// Summarize text
$summary = Sidekick::summarize('Long text here...');

// Translate text
$translated = Sidekick::translate('Hello', 'French');

// Extract keywords
$keywords = Sidekick::extractKeywords('Some article text...');
```

## Chat Widget

Sidekick ships with an Alpine.js-powered chat widget you can embed in any Blade template.

### Enable the widget

In your `.env`:

```dotenv
SIDEKICK_WIDGET_ENABLED=true
SIDEKICK_WIDGET_PROVIDER=openai
SIDEKICK_WIDGET_MODEL=gpt-4o
```

### Add to a Blade template

```blade
<x-sidekick::chat-widget
    position="bottom-right"
    theme="dark"
    title="AI Assistant"
    placeholder="Ask me anything..."
/>
```

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

Custom providers should implement `ProviderContract` and the relevant capability interfaces (`ProvidesText`, `ProvidesImages`, etc.).

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

    $fake->assertTextGenerated();
    $fake->assertProviderUsed('openai');
    $fake->assertModelUsed('gpt-4o');
    $fake->assertPromptContains('expected text');
}
```

## Events

Sidekick dispatches events you can listen to:

| Event | When |
|-------|------|
| `RequestSending` | Before a request is sent to the provider |
| `ResponseReceived` | After a successful response |
| `StreamChunkReceived` | For each streaming chunk |
| `RequestFailed` | When a request fails |

## Provider Capabilities

| Capability | OpenAI | Anthropic | Mistral | Cohere |
|-----------|--------|-----------|---------|--------|
| Text | Yes | Yes | Yes | Yes |
| Image | Yes | - | - | - |
| Audio | Yes | - | - | - |
| Transcription | Yes | - | - | - |
| Embedding | Yes | - | Yes | - |
| Moderation | Yes | - | - | - |

## API Key Resources

- [OpenAI](https://platform.openai.com)
- [Anthropic](https://console.anthropic.com)
- [Mistral](https://console.mistral.ai)
- [Cohere](https://dashboard.cohere.com)

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE) for details.

## Stargazers

[![Stargazers repo roster for @PapaRascal2020/sidekick](https://reporoster.com/stars/dark/notext/PapaRascal2020/sidekick)](https://github.com/PapaRascal2020/sidekick/stargazers)
