<?php

namespace PapaRascalDev\Sidekick\Features;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class Transcribe
{
    /**
     * @param string $url
     * @param array $headers
     */
    function __construct(protected string $url, protected array $headers)
    {}


    /**
     * audioFile
     *
     * Transcribes the audio file to text.
     *
     * @param string $model
     * @param string $filePath
     * @return array
     * @throws ConnectionException
     */
    public function audioFile(
        string $model,
        string $filePath
    ): array
    {

        return Http::withHeaders($this->headers)
            ->asMultipart()
            ->attach('file', file_get_contents($filePath), File::basename($filePath))
            ->post($this->url, [
                'model' => $model
            ])->json();
    }
}
