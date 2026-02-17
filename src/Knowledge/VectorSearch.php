<?php

namespace PapaRascalDev\Sidekick\Knowledge;

use Illuminate\Support\Collection;
use PapaRascalDev\Sidekick\Contracts\SearchesKnowledge;
use PapaRascalDev\Sidekick\Models\KnowledgeBase;

class VectorSearch implements SearchesKnowledge
{
    public function search(KnowledgeBase $knowledgeBase, array $queryEmbedding, int $limit = 5, float $minScore = 0.3): Collection
    {
        if (empty($queryEmbedding)) {
            return collect();
        }

        $chunks = $knowledgeBase->chunks()
            ->whereNotNull('embedding')
            ->get();

        return $chunks
            ->map(function ($chunk) use ($queryEmbedding) {
                $similarity = $this->cosineSimilarity($queryEmbedding, $chunk->embedding ?? []);
                $chunk->similarity = $similarity;

                return $chunk;
            })
            ->filter(fn ($chunk) => $chunk->similarity >= $minScore)
            ->sortByDesc('similarity')
            ->take($limit)
            ->values();
    }

    public function cosineSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b) || count($a) !== count($b)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0, $count = count($a); $i < $count; $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dotProduct / ($normA * $normB);
    }
}
