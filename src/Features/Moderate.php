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

    /**
     * Text and Image Moderation
     *
     * This will accept the text as a string and
     * images will accept an array like so:
     *
     * [
     *      'url' => 'https://some.domain/image1.png',
     *      'url' => 'https://some.domain/image2.png',
     *      'url' => 'https://some.domain/image3.png',
     * ]
     *
     * Accepted url values are either the URL or base64
     *
     * Base64 Example; "url": "data:image/jpeg;base64,abcdefg..."
     *
     *
     * @param string $text
     * @param array $images
     * @return array|mixed
     * @throws ConnectionException
     */
    public function textAndImages (string $text, array $images ): mixed
    {
        return Http::withHeaders($this->headers)
            ->post($this->url, [
                'model' => 'omni-moderation-latest',
                'input' => [
                    ['type' => 'text', 'text' => $text],
                    ['image_url' => $images],
                ]
            ])->json();
    }
}
