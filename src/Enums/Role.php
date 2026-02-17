<?php

namespace PapaRascalDev\Sidekick\Enums;

enum Role: string
{
    case System = 'system';
    case User = 'user';
    case Assistant = 'assistant';
}
