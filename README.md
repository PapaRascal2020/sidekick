
<p align="center">
    <a href="https://laravel.com"><img alt="Laravel Plugin" src="https://img.shields.io/badge/Laravel%20Plugin-red?logo=laravel&logoColor=white"/></a>&nbsp;&nbsp;&nbsp;
    <img alt="Status" src="https://img.shields.io/badge/Project%20Status-Active-green"/> &nbsp;&nbsp;&nbsp;
    <img alt="Latest Version" src="https://img.shields.io/packagist/v/paparascaldev/sidekick?label=Latest%20Release"/> &nbsp;&nbsp;
    <img alt="Stability" src="https://img.shields.io/badge/Stability-beta-yellow"/> &nbsp;&nbsp;&nbsp;
    <a href="https://packagist.org/packages/paparascaldev/sidekick"><img alt="Status" src="https://img.shields.io/badge/Packagist-F28D1A?logo=Packagist&logoColor=white"/></a>
</p>
<br>
<p align="center"><a href="https://github.com/PapaRascal2020/sidekick/wiki/2.-Install-Guide">Installation Guide</a> | <a href="https://github.com/PapaRascal2020/sidekick/wiki/3.-Documentation-(pre-v0.2.2)">Documentation</a> | <a href="https://ashleyjohnson.co.uk/journal/calling-all-laravel-devs-lets-collaborate-on-sidekick">Contributung</a> | <a href="https://github.com/PapaRascal2020/sidekick?tab=GPL-3.0-1-ov-file">License</a></p>
<br>

<p align="center">
<img src="https://github.com/user-attachments/assets/27dfb981-e183-4b85-870e-24aab419bb6a" alt="" />
</p>

### Description
This project provides a unified wrapper around the OpenAI, Claude, Cohere and Mistral APIs for Laravel. The goal is to simplify switching between different AI models and APIs, making it as seamless as possible.

 <img src="https://github.com/user-attachments/assets/3f7d016b-735d-4f3a-a059-064d15f16040" alt="" height="25" /> <a href="https://www.youtube.com/watch?v=rfhhsQYpq6c"> **Watch a short video on how to get set up in under 5 minutes.** </a>
 
#### ![image](https://github.com/user-attachments/assets/5595c5d1-60c8-4693-aa54-47dffa6f4d10) Features
- Open AI, Mistral, Claude & Cohere
    - Conversation => Quickly prototype/build a chatbot (with history via DB)
    - Completion => Complete or respond to a given prompt
- Open AI, Mistral (ONLY)
    - Embedding => Create a vector representation from text
- Open AI (ONLY)    
    - Audio => Create an Audio file from a text prompt
    - Image => Create an Image from a text prompt
    - Moderation => Moderate text and flag for unwanted content (great for moderating comments)
    - Transcription => Transcribe an audio file
 - Quick Utilities (supported by all models)
    - Summarize => Summarizes the given input
    - extractKeywords => Extracts Keywords from a given input
    - translateText => Translates the given input into another language
    - generateContent => Generates content ideas from a given topic 
 


### Installation Guide
The easiest way to install the package in your laravel app is to run the following command from within your projects directory:
```bash
    composer require paparascaldev/sidekick
```

The add the following to the `bootstrap/providers.php`

```PHP
   \PapaRascalDev\Sidekick\SidekickServiceProvider::class,
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
/sidekick
```

### Documentation

Please refer to the wiki documentation that can be found here: [Documentation](https://github.com/PapaRascal2020/sidekick/wiki/3.-Documentation-(pre-v0.2.2))


### Ways to Contribute

To find out about how you can get involved checkout the <a href="https://github.com/PapaRascal2020/sidekick/blob/main/CONTRIBUTING.md">CONTRIBUTING.md</a> or read my post on it here: [https://ashleyjohnson.co.uk/journal/calling-all-laravel-devs-lets-collaborate-on-sidekick](https://ashleyjohnson.co.uk/journal/calling-all-laravel-devs-lets-collaborate-on-sidekick)

Also, star my repository if you think it is useful.

### Model Support

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

