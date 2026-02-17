<?php

namespace PapaRascalDev\Sidekick\Contracts;

use PapaRascalDev\Sidekick\Responses\AudioResponse;

interface ProvidesAudio
{
    public function generateAudio(string $model, string $text, string $voice = 'alloy', string $format = 'mp3'): AudioResponse;
}
