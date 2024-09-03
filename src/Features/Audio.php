<?php

namespace PapaRascalDev\Sidekick\Features;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class Audio
{
    /**
     * @param string $url
     * @param array $headers
     */
    function __construct(protected string $url, protected array $headers)
    {}

    /**
     * Audio()->fromText
     *
     * Returns audio from a given inpur.
     *
     * @param string $model
     * @param string $text
     * @param String $voice
     * @return string
     * @throws ConnectionException
     */
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
