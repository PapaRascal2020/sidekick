<?php

namespace PapaRascalDev\Sidekick\Contracts;

use PapaRascalDev\Sidekick\Responses\ImageResponse;

interface ProvidesImages
{
    public function generateImage(string $model, string $prompt, string $size = '1024x1024', string $quality = 'standard', int $count = 1): ImageResponse;
}
