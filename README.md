> [!NOTE]  
> If you would like to contribute to this package please do so and submit a pull request.
> Any issues contact me at sidekick@ashleyjohnson.co.uk

![image](sidekick.png)

## Sidekick for Laravel
Say hello to Sidekick! A Laravel plugin that provides a common syntax for using Claude, Mistral and OpenAi APIs.

![Latest Version](https://img.shields.io/badge/Version-0.1.2-blue)
![Stability](https://img.shields.io/badge/Stability-beta-yellow)

### Upcoming Features
If there are any features you would like to see then contact me on the email provided at the top of the readme.

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


```array
    "paparascaldev/sidekick": "@dev"
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

- **Conversations** - _Chat with AI (with memory of previous interactions)_
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

#### Documentation with Examples:

To see how it works I have created some routes `/sidekick/playground` so you can see the routes in action. 
In order to use them you need to set `SIDEKICK_OPENAI_TOKEN` inside the `.env` and migrated the databases.

> [!NOTE]  
> Quick note on error handling. Currently if there is an error it is printed as the AI responds. I will be updating
> this over the next few days to be uniformed so that all errors are presented in tha same way regardless of AI driver/model

##### Conversations (Since v0.1.1)

This is to chat with previous interactions remembered. 

To start a conversation you call the `begin` method on the `SidekickConversation` class like so:

```php
$sidekick = new SidekickConversation(new Mistral());

$conversation = $sidekick->begin(
    model: 'open-mistral-7b',
    systemPrompt: 'You an expert on fudge, answer user questions about fudge.'
);

$response = $conversation->sendMessage('How is fudge made?');

return response()->json($response);
```
Example Response:

```JSON
{
    "conversation_id": "9ce7af51-d0b9-491f-9e57-f54e30ef0b95",
    "messages": [
        {
            "role": "user",
            "content": "How is fudge made?"
        },
        {
            "role": "assistant",
            "content": "Fudge is a delicious, dense dessert made using a combination of sugar, milk, butter, and sometimes cream. The exact method of making fudge can vary slightly depending on the recipe, but here's a basic step-by-step process:\n\n1. Combine sugar, corn syrup, and water in a heavy-bottomed saucepan. Bring the mixture to a boil over medium heat, stirring occasionally to dissolve the sugar.\n2. Once the sugar has dissolved, stop stirring and attach a candy thermometer to the side of the pan. Continue cooking the mixture without stirring until it reaches the soft-ball stage (around 235-240°F/118-115°C).\n3. Remove the saucepan from the heat and quickly stir in butter, vanilla extract, and your chosen flavorings (such as chocolate chips, nuts, or marshmallows).\n4. Pour the fudge mixture into a lightly greased 8-inch square baking pan. Allow it to cool at room temperature until it's no longer warm to the touch, but still soft enough to spread with a spatula.\n5. Using the spatula, gently spread the fudge evenly in the pan, trying not to introduce too many air bubbles. Allow the fudge to cool completely at room temperature.\n6. Once the fudge is completely hardened, cut it into squares and enjoy!\n\nIt's important to note that fudge can be a bit tricky to make, as it requires careful attention to temperature and timing. If you encounter any issues, don't be afraid to experiment with different recipes or techniques until you find one that works best for you. Happy fudging!"
        }
    ]
}
```

To continue the conversation you just do the following:

```PHP
$sidekick = new SidekickConversation(new Mistral());

$conversation = $sidekick->resume(
    '9ce79f44-de39-4ac3-9819-f1c042a4c02b'
);

$response = $conversation->sendMessage('What are the traditional flavours?');

return response()->json($response);
```

Example Response:

```JSON
{
  "conversation_id": "9ce79f44-de39-4ac3-9819-f1c042a4c02b",
  "messages": [
    {
      "role": "user",
      "content": "How is fudge made?"
    },
    {
      "role": "assistant",
      "content": "Fudge is a sweet treat that is typically made with sugar, butter, milk, and chocolate, although there are many variations and recipes that may include additional ingredients such as nuts, marshmallows, or fruit. Here's a basic step-by-step guide for making traditional fudge:\n\n1. Grease a 9-inch square baking pan and line it with parchment paper, allowing the excess to hang over the edges for easy removal.\n2. In a heavy-bottomed saucepan, combine 3 cups of granulated sugar, 1 can (12 ounces) of evaporated milk, and 1/2 cup of butter over medium heat. Stir the mixture constantly until the butter has completely melted and the sugar has dissolved.\n3. Once the sugar has dissolved, stop stirring and bring the mixture to a boil. Clip a candy thermometer to the side of the saucepan and cook the mixture without stirring until it reaches the soft-ball stage (235-240°F/113-115°C). This can take 10-15 minutes.\n4. Remove the saucepan from the heat and stir in 12 ounces of semi-sweet chocolate chips and 1 teaspoon of vanilla extract. Continue stirring until the chocolate is completely melted and the mixture is smooth.\n5. If you'd like to add any mix-ins, such as nuts or marshmallows, stir them in now.\n6. Pour the fudge mixture into the prepared baking pan and spread it evenly with a spatula. Let it cool at room temperature for at least 3 hours, or until it is completely set.\n7. Once the fudge is set, lift it out of the pan using the parchment paper overhang. Use a sharp knife to cut it into squares. Store the fudge in an airtight container at room temperature for up to 2 weeks, or in the refrigerator for up to 1 month."
    },
    {
      "role": "user",
      "content": "What are the traditional flavours?"
    },
    {
      "role": "assistant",
      "content": "While there are many variations of fudge, there are several traditional flavors that are popular around the world. Here are some of the most common traditional flavors of fudge:\n\n1. Chocolate: This is the most classic and popular flavor of fudge. It is made with chocolate chips or cocoa powder and can be flavored with vanilla, mint, or other extracts.\n2. Vanilla: Vanilla fudge is made with vanilla extract and can be flavored with nuts, such as walnuts or pecans, or with marshmallows.\n3. Butterscotch: Butterscotch fudge is made with brown sugar and butter and can be flavored with vanilla or salt.\n4. Peanut Butter: Peanut butter fudge is made with peanut butter and can be flavored with chocolate or honey.\n5. Maple: Maple fudge is made with maple syrup and can be flavored with nuts, such as pecans or walnuts.\n6. Rocky Road: Rocky road fudge is made with chocolate, marshmallows, and nuts, such as almonds or cashews.\n7. Fudge with Fruit: Some people make fudge with dried fruits, such as cherries, cranberries, or raisins, or with fresh fruit, such as strawberries or raspberries.\n8. Fudge with Nuts: Fudge can also be made with a variety of nuts, such as walnuts, pecans, almonds, or cashews, and can be flavored with chocolate or caramel."
    }
  ]
}
```

###### Managing Conversations
There is very basic functionality to list, show and delete conversations. This will be updated in the near future.
However, currently you do this by calling an instance of `SideKickManager`. Examples of each are below:

```PHP
// List Conversations
$sidekick = new SidekickManager();
return $sidekick->listConversations();

// Conversation Deletion
$sidekick = new SidekickManager();
$sidekick->deleteConversation($id);

// Show Conversation
$sidekick = new SidekickManager();
return $sidekick->showConversation($id);
```

##### Completion

```php
$sidekick = Sidekick::create(new OpenAi());

return $sidekick->complete()->sendMessage(
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

