<?php

namespace PapaRascalDev\Sidekick\Contracts;

use Illuminate\Support\Collection;
use PapaRascalDev\Sidekick\Models\KnowledgeBase;

interface SearchesKnowledge
{
    /**
     * @param  float[]  $queryEmbedding
     */
    public function search(KnowledgeBase $knowledgeBase, array $queryEmbedding, int $limit = 5, float $minScore = 0.3): Collection;
}
