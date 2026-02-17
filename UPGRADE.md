# Upgrading from v1.x to v2.0

Sidekick v2.0 is a complete rewrite with breaking changes. This guide covers everything you need to migrate.

## Requirements

- **PHP 8.2+** (was 8.0+)
- **Laravel 10+** (dropped Laravel 9 support)

## Step 1: Update Composer

```bash
composer require paparascaldev/sidekick:^2.0
```

## Step 2: Publish the new config

```bash
php artisan vendor:publish --tag=sidekick-config --force
```

This creates `config/sidekick.php`. The v1 package had no config file.

## Step 3: Update your .env

The environment variable names remain the same:

```dotenv
SIDEKICK_OPENAI_TOKEN=your-key
SIDEKICK_CLAUDE_TOKEN=your-key
SIDEKICK_MISTRAL_TOKEN=your-key
SIDEKICK_COHERE_TOKEN=your-key
```

## Step 4: Run migrations

```bash
php artisan migrate
```

The conversations table has been updated:
- `class` column renamed to `provider` (stores provider name instead of FQCN)
- `max_tokens` changed from `bigInteger` to `unsignedInteger` with a default of 1024

**Note:** Existing conversation data is not automatically migrated. If you have important conversation history, back it up before migrating.

## Step 5: Update your code

### Driver instantiation (removed)

```php
// v1
use PapaRascalDev\Sidekick\Drivers\OpenAi;
$driver = new OpenAi();
$sidekick = Sidekick::create($driver);
$response = $sidekick->complete('gpt-4', 'system prompt', 'user message', [], 1024);

// v2
use PapaRascalDev\Sidekick\Facades\Sidekick;
$response = Sidekick::text()
    ->using('openai', 'gpt-4o')
    ->withSystemPrompt('system prompt')
    ->withPrompt('user message')
    ->generate();

echo $response->text;  // Typed DTO instead of raw array
```

### Conversations

```php
// v1
$convo = new SidekickConversation();
$convo->begin(new OpenAi(), 'gpt-4', 'System prompt');
$response = $convo->sendMessage('Hello');

// v2
$convo = Sidekick::conversation()
    ->using('openai', 'gpt-4o')
    ->withSystemPrompt('System prompt')
    ->begin();

$response = $convo->send('Hello');
echo $response->text;
```

### Image generation

```php
// v1
$sidekick = Sidekick::create(new OpenAi());
$response = $sidekick->image()->generate('dall-e-3', 'A sunset');

// v2
$response = Sidekick::image()
    ->using('openai', 'dall-e-3')
    ->withPrompt('A sunset')
    ->generate();

echo $response->url();
```

### Audio generation

```php
// v1
$sidekick = Sidekick::create(new OpenAi());
$response = $sidekick->audio()->fromText('tts-1', 'Hello');

// v2
$response = Sidekick::audio()
    ->using('openai', 'tts-1')
    ->withText('Hello')
    ->generate();

$response->save('audio/hello.mp3');
```

### Utilities

```php
// v1
$utils = sidekickUtilities(new OpenAi());
$summary = $utils->summarize('Long text...');

// v2
$summary = Sidekick::summarize('Long text...');
$translated = Sidekick::translate('Hello', 'French');
$keywords = Sidekick::extractKeywords('Some text');
```

### Helper functions

```php
// v1
$driver = sidekick(new OpenAi());
$convo = sidekickConversation();
$utils = sidekickUtilities(new OpenAi());

// v2
$manager = sidekick(); // Returns SidekickManager
$manager->text()->using('openai', 'gpt-4o')->withPrompt('Hi')->generate();
```

## Step 6: Remove old playground (if installed)

If you previously ran `php artisan sidekick:install` for the v1 playground:

1. Delete `resources/views/Pages/Sidekick/` (if it exists)
2. Delete `resources/views/Components/Sidekick/` (if it exists)
3. Delete `routes/web.sidekick.php` (if it exists)
4. Remove the `require base_path('routes/web.sidekick.php');` line from `routes/web.php`

The v2 chat widget replaces the playground. See README.md for setup instructions.

## Removed Classes

The following classes have been removed entirely:

| v1 Class | v2 Replacement |
|----------|---------------|
| `Sidekick` (static factory) | `SidekickManager` via facade |
| `SidekickConversation` | `ConversationBuilder` |
| `SidekickDriverInterface` | `ProviderContract` + capability interfaces |
| `Drivers\OpenAi` | `Providers\OpenAiProvider` |
| `Drivers\Claude` | `Providers\AnthropicProvider` |
| `Drivers\Mistral` | `Providers\MistralProvider` |
| `Drivers\Cohere` | `Providers\CohereProvider` |
| `Features\Completion` | Integrated into providers |
| `Features\Image` | `ImageBuilder` |
| `Features\Audio` | `AudioBuilder` |
| `Features\Embedding` | `EmbeddingBuilder` |
| `Features\Moderate` | `ModerationBuilder` |
| `Features\Transcribe` | `TranscriptionBuilder` |
| `Utilities\Utilities` | `SidekickManager` utility methods |
| `Facades\SidekickConversation` | Use `Sidekick::conversation()` |
| `Models\SidekickConversation` | `Models\Conversation` |
| `Models\SidekickConversationMessage` | `Models\ConversationMessage` |

## New Features in v2

- Fluent builder API for all capabilities
- Typed readonly response DTOs
- Streaming support with SSE
- Event system (`RequestSending`, `ResponseReceived`, etc.)
- `Sidekick::fake()` for testing
- Alpine.js chat widget
- Custom provider registration
- Publishable config file
