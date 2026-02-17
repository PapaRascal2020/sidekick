<?php

namespace PapaRascalDev\Sidekick\Enums;

enum Capability: string
{
    case Text = 'text';
    case Image = 'image';
    case Audio = 'audio';
    case Transcription = 'transcription';
    case Embedding = 'embedding';
    case Moderation = 'moderation';
}
