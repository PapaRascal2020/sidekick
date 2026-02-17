<?php

namespace PapaRascalDev\Sidekick\Enums;

enum Provider: string
{
    case OpenAI = 'openai';
    case Anthropic = 'anthropic';
    case Mistral = 'mistral';
    case Cohere = 'cohere';
}
