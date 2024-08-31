<?php

namespace PapaRascalDev\Sidekick\Features;

use Illuminate\Support\Facades\Http;

class Embedding
{

    function __construct(protected string $url, protected array $headers)
    {}

    public function make(
        string $model,
        string $input
    ): array
    {
        return $this->generate($model, $input);
    }
    public function generate(
        string $model,
        string $input
    ): array
    {
        $response = Http::withHeaders($this->headers)
            ->post($this->url, [
                'model' => $model,
                'input' => $input
            ])->json();

        return $response;
    }
}
