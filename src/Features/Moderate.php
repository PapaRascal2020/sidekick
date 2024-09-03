<?php

namespace PapaRascalDev\Sidekick\Features;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class Moderate
{
    /**
     * @param string $url
     * @param array $headers
     */
    function __construct(protected string $url, protected array $headers)
    {}

    /**
     * Text
     *
     * Sends the given input to be moderated by the AI model.
     *
     * @param string $model
     * @param string $content
     * @return array
     * @throws ConnectionException
     */
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
