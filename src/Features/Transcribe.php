<?php

namespace PapaRascalDev\Sidekick\Features;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class Transcribe
{
    function __construct(protected string $url, protected array $headers)
    {}
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
