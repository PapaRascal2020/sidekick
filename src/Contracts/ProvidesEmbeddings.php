<?php

namespace PapaRascalDev\Sidekick\Contracts;

use PapaRascalDev\Sidekick\Responses\EmbeddingResponse;

interface ProvidesEmbeddings
{
    public function generateEmbedding(string $model, string|array $input): EmbeddingResponse;
}
