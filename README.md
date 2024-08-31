> [!NOTE]  
> If you would like to contribute to this package please do so and submit a pull request.
> Any issues contact me at sidekick@ashleyjohnson.co.uk

![image](sidekick.png)

## Sidekick for Laravel
Say hello to Sidekick! A Laravel plugin that provides a common syntax for using Claude, Mistral and OpenAi APIs.

![Latest Version](https://img.shields.io/badge/Version-0.1.0-blue)
![Stability](https://img.shields.io/badge/Stability-beta-red)

### Upcoming Features

- Implementation of a database migration and model to store and persist conversations
- Support for more models and/or add another Driver from another service.

### About
Provides a uniformed wrapper around OpenAi, Claude & Mistral APIs (Previously: EloquentAi).
The aim of this project is to create a package where switching between
AIs and there models as simple as possible.

This will be achieved by created a common syntax for calling different services
(Completions, Text To Speech, Speech To Text, Text To Image) in a way that is similar to eloquent.

**AI Models Tested:**

#### Open Ai
gpt-3.5-turbo, gpt-4, tts-1, tts-1-hd, dall-e-2, dall-e-3, whisper-1, text-embedding-3-small, text-embedding-3-large, text-embedding-ada-002, text-moderation-latest, text-moderation-stable, text-moderation-007
#### Mistral AI
mistral-small-latest, mistral-medium-latest, mistral-large-latest, open-mistral-7b, mistral-embed
#### Claude AI
claude-3-opus-20240229, claude-3-sonnet-20240229, claude-3-haiku-20240307

Examples of the syntax are at the bottom of this readme.

### Installation

#### Released Version

Add the following to the `require` section.

```array
    "paparascaldev/sidekick": "^0.1.0"
```

Save `composer.json`

#### Development Version (for testing)

In your Laravel app do the following:

In `composer.json` add the following repository to the `repositories` section:

```php
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/PapaRascal2020/sidekick"
    }
],
```
Then add the following to the `require` section.

```array
    "paparascaldev/sidekick": "dev-main"
```

Save `composer.json`

===========================================================

Then after you have selected an installation method from above, open the terminal and type the following:

```bash
 composer update
```

Once this is done, open `bootstrap/providers.php` and add the following:

```php
\PapaRascalDev\Sidekick\SidekickServiceProvider::class,
```

That's it! You are now ready to use the package.

### Getting Started

There are six services and they are:

- **Completions** - _To chat with AI_
- **Embedding** - _To create vector representations of your text_
- **Image** - _To generate images by user input._
- **Audio** - _Take text and convert to audio_
- **Transcription** - _Take an audio file and return text_
- **Moderation** - _Moderate a string of text (i.e Comment) for harmful content_

Currently, Open AI offers all of them where as Claude AI & Mistral AI are for some.
To get the best out of this plugin you will need at least an Open AI api key, you 
can get this by going to https://platform.openai.com and registering an account.

For Mistral AI (https://console.mistral.ai/) & Claude AI (https://console.anthropic.com/)
models you would need to get sign up on the relevant sites (above)

Start by updating your `.env` file with the following fields.

```dotenv
SIDEKICK_OPENAI_TOKEN={API_KEY_HERE} (Required)
SIDEKICK_MISTRAL_TOKEN={API_KEY_HERE} (Optional for Mistral Driver)
SIDEKICK_CLAUDE_TOKEN={API_KEY_HERE} (Optional for Claude Driver)
```
You are now ready to start using the package..

#### Examples:

##### Completion

```php
$sidekick = Sidekick::create(new OpenAi());

return $sidekick->converse()->sendMessage(
    model: 'gpt-3.5-turbo',
    systemPrompt: 'You an expert on fudge, answer user questions about fudge.',
    messages:[['role' => 'user', 'content' => "How is fudge made?"]]
);
```

##### Embedding

```php
$sidekick = Sidekick::create(new Mistral());

return $sidekick->embedding()->make(
    'mistral-embed',
    'This is sample content to embed'
);
```

##### Image (Image From Text)

```php
 $sidekick = Sidekick::create(new OpenAi());
 
$image =  $sidekick->image()->make(
    'dall-e-3',
    'A man on a waterboard',
    '1024',
    '1024'
);

// This is just a basic example of printing to screen.
// In a real world situation you may save it and then render out.
return "<img src='{$image['data'][0]['url']}' />";
```
##### Audio (Text To Speech)

```php
$sidekick = Sidekick::create(new OpenAi());

$audio = $sidekick->audio()->fromText(
    'tts-1',
    'Have a nice day!'
);

// This is just a basic example of streaming it to the browser.
// In a real world situation you may save it and then reference the file
// instead.
header('Content-Type: audio/mpeg');
echo $audio
```

##### Transcription (Speech To Text)

```php
$sidekick = Sidekick::create(new OpenAi());

return $sidekick->transcribe()->audioFile(
    'whisper-1',
    'http://english.voiceoversamples.com/ENG_UK_M_PeterB.mp3'
);
```
###### Example Response
```json
{
  "text":"The stale smell of old beer lingers. It takes heat to bring out the odor. A cold dip restores health and zest. A salt pickle tastes fine with ham. Tacos al pastor are my favorite. A zestful food is the hot cross bun."
}
```

##### Moderation 
This is a service where you feed it text from a comment for example and it will return 
with an array of boolean values for certain moderation points.

```php
$sidekick = Sidekick::create(new OpenAi());

return $sidekick->moderate()->text(
    'text-moderation-latest',
    'Have a great day.',
);
```
###### Example Response

```json
{
  "id":"modr-94DxgkEGhw7yJDlq8oCrLOVXnqli5",
  "model":"text-moderation-007",
  "results":[
    {
      "flagged":true,
      "categories":{
        "sexual":false,
        "hate":false,
        "harassment":true,
        "self-harm":false,
        "sexual\/minors":false,
        "hate\/threatening":false,
        "violence\/graphic":false,
        "self-harm\/intent":false,
        "self-harm\/instructions":false,
        "harassment\/threatening":false,
        "violence":false
      },
      "category_scores":{
        "sexual":0.02169245481491089,
        "hate":0.024598680436611176,
        "harassment":0.9903337359428406,
        "self-harm":5.543852603295818e-5,
        "sexual\/minors":2.5174302209052257e-5,
        "hate\/threatening":2.9870452635805123e-6,
        "violence\/graphic":6.8601830207626335e-6,
        "self-harm\/intent":0.0002317160106031224,
        "self-harm\/instructions":0.00011696072033373639,
        "harassment\/threatening":1.837775380408857e-5,
        "violence":0.00020553809008561075
      }
    }
  ]}
```

