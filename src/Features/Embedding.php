<?php

namespace PapaRascalDev\Sidekick\Features;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class Embedding
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
     * Returns a vector representation of a string
     *
     * @param string $model
     * @param string $input
     * @return array
     * @throws ConnectionException
     */
    public function make(
        string $model,
        string $input
    ): array
    {
        return $this->generate($model, $input);
    }

    /**
     * Generate
     *
     * Returns a vector representation of a string
     *
     * @param string $model
     * @param string $input
     * @return array
     * @throws ConnectionException
     */
    public function generate(
        string $model,
        string $input
    ): array
    {
        return Http::withHeaders($this->headers)
            ->post($this->url, [
                'model' => $model,
                'input' => $input
            ])->json();
    }
}
