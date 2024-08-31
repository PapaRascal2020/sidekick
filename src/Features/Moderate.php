<?php

namespace PapaRascalDev\Sidekick\Features;

use Illuminate\Support\Facades\Http;

class Moderate
{
    function __construct(protected string $url, protected array $headers)
    {}

    public function text(
        string $model,
        string $content,
    ): array
    {
        return Http::withHeaders($this->headers)
            ->post($this->url, [
                'model' => $model,
                'input' => $content
            ])->json();
    }
}
