<?php

namespace PapaRascalDev\Sidekick\Features;

use Illuminate\Support\Facades\Http;

class Image
{

    function __construct(protected string $url, protected array $headers)
    {}
    public function make(
        string $model,
        string $prompt,
        int $width = 1024,
        int $height = 1024,
        string $quality = 'standard') : array
    {
        return self::generate($model, $prompt, $width, $height, $quality);
    }

    public function generate(
        string $model,
        string $prompt,
        int $width = 1024,
        int $height = 1024,
        string $quality = 'standard') : array
    {
        return Http::withHeaders($this->headers)
            ->post($this->url, [
                'model' => $model,
                'prompt' => $prompt,
                'size' => sprintf("%sx%s", $height, $width),
                'quality' => $quality
            ])->json();
    }
}
