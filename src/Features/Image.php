<?php

namespace PapaRascalDev\Sidekick\Features;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class Image
{

    /**
     * @param string $url
     * @param array $headers
     */
    function __construct(protected string $url, protected array $headers)
    {}

    /**
     * Make
     *
     * Creates an image from a description provided in text.
     *
     * @param string $model
     * @param string $prompt
     * @param int $width
     * @param int $height
     * @param string $quality
     * @return array
     * @throws ConnectionException
     */
    public function make(
        string $model,
        string $prompt,
        int $width = 1024,
        int $height = 1024,
        string $quality = 'standard') : array
    {
        return self::generate($model, $prompt, $width, $height, $quality);
    }

    /**
     * Generate
     *
     * Creates an image from a description provided in text.
     *
     * @param string $model
     * @param string $prompt
     * @param int $width
     * @param int $height
     * @param string $quality
     * @return array
     * @throws ConnectionException
     */

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
