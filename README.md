
<p align="center">
    <a href="https://laravel.com"><img alt="Laravel Plugin" src="https://img.shields.io/badge/Laravel%20Plugin-red?logo=laravel&logoColor=white"/></a>&nbsp;&nbsp;&nbsp;
    <img alt="Status" src="https://img.shields.io/badge/Project%20Status-Active-green"/> &nbsp;&nbsp;&nbsp;
    <img alt="Latest Version" src="https://img.shields.io/packagist/v/paparascaldev/sidekick?label=Latest%20Release"/> &nbsp;&nbsp;
    <img alt="Stability" src="https://img.shields.io/badge/Stability-beta-yellow"/> &nbsp;&nbsp;&nbsp;
    <a href="https://packagist.org/packages/paparascaldev/sidekick"><img alt="Status" src="https://img.shields.io/badge/Packagist-F28D1A?logo=Packagist&logoColor=white"/></a>
</p>
<br>
<p align="center"><a href="https://github.com/PapaRascal2020/sidekick/wiki/2.-Install-Guide">Installation Guide</a> | <a href="https://github.com/PapaRascal2020/sidekick/wiki/2.-Install-Guid](https://github.com/PapaRascal2020/sidekick/wiki/3.-Documentation">Documentation</a> | <a href="https://ashleyjohnson.co.uk/journal/3">Contributung</a> | <a href="https://github.com/PapaRascal2020/sidekick?tab=GPL-3.0-1-ov-file">License</a></p>
<br>

![sidekickforlaravel](https://github.com/user-attachments/assets/27dfb981-e183-4b85-870e-24aab419bb6a)

### Description
This project provides a unified wrapper around the OpenAI, Claude, Cohere and Mistral APIs for Laravel. The goal is to simplify switching between different AI models and APIs, making it as seamless as possible.
<br/>
<br>

### Installation Guide
The easiest way to install the package in your laravel app is to run the following command from within your projects directory:
```bash
    composer require paparascaldev/sidekick
```

#### Other Methods
If you would like to install a specific version you can manually update your `composer.json` file under `require` section like so:
```json
    "paparascaldev/sidekick": "^0.1.1"
```
_You can also use `@dev` tag._

After you have updated the file run:
```bash
    composer upadte
```

### Configuring the `.env`
Once Sidekick is installed you need to update your `.env` file with your access tokens.

You only need to specify the token for the provider(s) you will use. 

```dotenv
SIDEKICK_OPENAI_TOKEN={API_KEY_HERE} (Recommended)
SIDEKICK_MISTRAL_TOKEN={API_KEY_HERE} (Optional)
SIDEKICK_CLAUDE_TOKEN={API_KEY_HERE} (Optional)
SIDEKICK_COHERE_TOKEN={API_KEY_HERE} (Optional)
```

#### Where can I get an API key?
If you are not yet signed up with any of the AI providers, here are some links to help:

- https://platform.openai.com (Open Ai)
- https://console.anthropic.com (Claude)
- https://console.mistral.ai (Mistral)
- https://dashboard.cohere.com (Cohere)

### Running Migrations
Run your migrations using the command below:

```bash
    php artisan migrate
```

After the `.env` is updated you can start testing the plugin.

### The Sidekick Playground

In order to see some examples of Sidekick in action I have created a playground.
This is not available by default because some may not wish to use it.

If you would like to use it run the following artisan command to install the playground:

```bash
  php artisan sidekick:install
```
This will install the routes and views into your application.

After the install you can access the playground at: 

```
/sidekick/playground
```

### Documentation

For the documentation we will be using the OpenAi model as it supports all endpoints, where Claude and Mistral are supported you can just hot-swap OpenAi when initialising the `Sidekick/SidekickConversation` class.

#### Sidekick Conversations

This allows you to create a chatbot that remembers previos interactions.

To start a new conversation:

```php
$sidekick = new SidekickConversation();

$conversation = $sidekick->begin(
    driver: new OpenAi(),
    model: 'gtp-3.5-turbo',
    systemPrompt: 'You can instruct the chatbot using this parameter'
);

$response = $conversation->sendMessage($user_input);

return response()->json($response);
```

This will create a new conversation in the `Database` and make the call to the AI for a response.

An example of the formatted response can be found below:

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

Once you have this response, to continue the conversation you can write the following in your controller making sure to pass the `conversation_id` in the request:

```PHP
$sidekick = new SidekickConversation();

$conversation = $sidekick->resume(
    conversationId: $conversation_id
);

$response = $conversation->sendMessage($user_input);

return response()->json($response);
```

An example of the formatted response can be found below:

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

#### Streamed Conversations

The difference in non streamed and streamed responses is a flag. This flag is passed in the sendMessage function like so:

```PHP
// Send a new message
return $conversation->sendMessage($request->get('message'));

// Send a new message (streamed)
return $conversation->sendMessage($request->get('message'), true);
```

#### Managing Sidekick Conversations
There is very basic functionality to list, show and delete conversations. This will be updated in the near future.
However, currently you do this by calling an instance of `SidekickChatManager`. Examples of each are below:

```PHP
public function index() {
    // List Conversations
    $sidekick = new SidekickChatManager();
    return $sidekick->showAll();
}

public function show(Conversation $conversation) {
    // show Conversation
    $sidekick = new SidekickManager();
    $sidekick->show($conversation);
}

public function delete(Conversation $conversation) {
    // Delete Conversation
    $sidekick = new SidekickManager();
    return $sidekick->delete($conversation);
}
```

#### Completion

```php
$sidekick = Sidekick::create(new OpenAi());

return $sidekick->complete()->sendMessage(
    model: 'gpt-3.5-turbo',
    systemPrompt: 'You an expert on fudge, answer user questions about fudge.',
    message:"How is fudge made?"
);
```

#### Embedding

```php
$sidekick = Sidekick::create(new Mistral());

return $sidekick->embedding()->make(
    model: 'mistral-embed',
    input: 'This is sample content to embed'
);
```

#### Image (Image From Text)

```php
 $sidekick = Sidekick::create(new OpenAi());
 
$image =  $sidekick->image()->make(
    model:'dall-e-3',
    prompt: $request->get('text_to_convert'),
    width:'1024',
    height:'1024'
);

// This is just a basic example of printing to screen.
// In a real world situation you may save it and then render out.
return "<img src='{$image['data'][0]['url']}' />";
```
#### Audio (Text To Speech)

```php
$sidekick = Sidekick::create(new OpenAi());

$audio = $sidekick->audio()->fromText(
    model: 'tts-1',
    text: 'Have a nice day!'
);

// This is just a basic example of streaming it to the browser.
// In a real world situation you may save it and then reference the file
// instead.
header('Content-Type: audio/mpeg');
echo $audio
```

#### Transcription (Speech To Text)

```php
$sidekick = Sidekick::create(new OpenAi());

return $sidekick->transcribe()->audioFile(
    model: 'whisper-1',
    filePath: 'http://english.voiceoversamples.com/ENG_UK_M_PeterB.mp3'
);
```
** Example Response **
```json
{
  "text":"The stale smell of old beer lingers. It takes heat to bring out the odor. A cold dip restores health and zest. A salt pickle tastes fine with ham. Tacos al pastor are my favorite. A zestful food is the hot cross bun."
}
```

#### Moderation 
This is a service where you feed it text from a comment for example and it will return 
with an array of boolean values for certain moderation points.

```php
$sidekick = Sidekick::create(new OpenAi());

return $sidekick->moderate()->text(
    model: 'text-moderation-latest',
    content: 'Have a great day.',
);
```
** Example Response **

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

#### Utilities

Utilities are quick ways of performing some actions using AI. The functions and there descriptions are below:

```PHP
// Summarises the content passed. Good for blurbs
$sidekick->utilities()->summarise(); 


// Extracts a number of keywords from a given string and returns a string of keywords (comma separated)       
$sidekick->utilities()->extractKeywords();

// Translates the given text to the language specified
$sidekick->utilities()->translateText();

// Generates content from a short description of what it should be about      
$sidekick->utilities()->generateContent();

// [OpenAI ONLY] Moderates content and returns a boolean of whether the content is flagged or not
$sidekick->utilities()->isContentFlagged();  

// [OpenAI ONLY] this method can store images and audio created by the AI.  
$sidekick->utilities()->store();             
```


### Ways to Contribute

I want this composer package for Laravel to be as useful as possible, so with that in mind here are the ways you can contribute:

- Submitting a Pull Request (if your a dev and want to help with this project)
- Raise issues to Github 
- Submit ideas/feedback to sidekick@ashleyjohnson.co.uk
- Star this repository (if you feel inclined)

### Testing Information

I have tested the package using the following models:

#### Open Ai
```gpt-3.5-turbo, gpt-4, tts-1, tts-1-hd, dall-e-2, dall-e-3, whisper-1, text-embedding-3-small, text-embedding-3-large, text-embedding-ada-002, text-moderation-latest, text-moderation-stable, text-moderation-007```
#### Mistral AI
```mistral-small-latest, mistral-medium-latest, mistral-large-latest, open-mistral-7b, mistral-embed```
#### Claude AI
```claude-3-opus-20240229, claude-3-sonnet-20240229, claude-3-haiku-20240307```
#### Cohere AI
```command-r-08-2024 command-r-plus-08-2024```

### Stargazers
A big shoutout to those who star the repository!

[![Stargazers repo roster for @PapaRascal2020/sidekick](https://reporoster.com/stars/dark/notext/PapaRascal2020/sidekick)](https://github.com/PapaRascal2020/sidekick/stargazers)

