<?php

namespace PapaRascalDev\Sidekick\Responses;

use Illuminate\Support\Facades\Storage;
use PapaRascalDev\Sidekick\ValueObjects\Meta;

readonly class AudioResponse
{
    public function __construct(
        public string $content,
        public string $format,
        public Meta $meta,
    ) {}

    public function save(string $path, ?string $disk = null): string
    {
        Storage::disk($disk)->put($path, $this->content);

        return $path;
    }
}
