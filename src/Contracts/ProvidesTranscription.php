<?php

namespace PapaRascalDev\Sidekick\Contracts;

use PapaRascalDev\Sidekick\Responses\TranscriptionResponse;

interface ProvidesTranscription
{
    public function transcribe(string $model, string $filePath, ?string $language = null): TranscriptionResponse;
}
