<?php

namespace PapaRascalDev\Sidekick\Features;

use Illuminate\Support\Facades\Http;

class Audio
{
    function __construct(protected string $url, protected array $headers)
    {}
    public function fromText(
        string $model,
        string $text,
        String $voice = "alloy"
    ): string
    {
        return Http::withHeaders($this->headers)
            ->post($this->url, [
                'model' => $model,
                'input' => $text,
                'voice' => $voice
            ]);
    }
}
