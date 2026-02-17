<?php

namespace PapaRascalDev\Sidekick\Builders;

use Illuminate\Support\Collection;
use PapaRascalDev\Sidekick\Contracts\SearchesKnowledge;
use PapaRascalDev\Sidekick\Knowledge\TextChunker;
use PapaRascalDev\Sidekick\Models\KnowledgeBase;
use PapaRascalDev\Sidekick\SidekickManager;

class KnowledgeBuilder
{
    private ?KnowledgeBase $knowledgeBase = null;
    private ?string $embeddingProvider = null;
    private ?string $embeddingModel = null;

    public function __construct(
        private readonly SidekickManager $manager,
    ) {}

    public function for(string $name): self
    {
        $this->knowledgeBase = KnowledgeBase::firstOrCreate(
            ['name' => $name],
            [
                'embedding_provider' => $this->embeddingProvider ?? config('sidekick.knowledge.embedding.provider', 'openai'),
                'embedding_model' => $this->embeddingModel ?? config('sidekick.knowledge.embedding.model', 'text-embedding-3-small'),
                'chunk_size' => config('sidekick.knowledge.chunking.chunk_size', 2000),
                'chunk_overlap' => config('sidekick.knowledge.chunking.overlap', 200),
            ]
        );

        return $this;
    }

    public function using(string $provider, ?string $model = null): self
    {
        $this->embeddingProvider = $provider;
        $this->embeddingModel = $model;

        if ($this->knowledgeBase) {
            $this->knowledgeBase->update([
                'embedding_provider' => $provider,
                'embedding_model' => $model,
            ]);
        }

        return $this;
    }

    public function ingest(string $text, ?string $source = null, array $metadata = []): self
    {
        $this->ensureKnowledgeBase();

        $chunker = new TextChunker(
            $this->knowledgeBase->chunk_size,
            $this->knowledgeBase->chunk_overlap,
        );

        $chunks = $chunker->chunk($text);

        foreach ($chunks as $index => $chunkText) {
            $embedding = $this->generateEmbedding($chunkText);

            $this->knowledgeBase->chunks()->create([
                'content' => $chunkText,
                'embedding' => $embedding,
                'source' => $source,
                'chunk_index' => $index,
                'metadata' => ! empty($metadata) ? $metadata : null,
            ]);
        }

        return $this;
    }

    /**
     * @param  string[]  $texts
     */
    public function ingestMany(array $texts, ?string $source = null, array $metadata = []): self
    {
        foreach ($texts as $text) {
            $this->ingest($text, $source, $metadata);
        }

        return $this;
    }

    public function ingestFile(string $filePath): self
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            throw new \RuntimeException("Unable to read file: {$filePath}");
        }

        return $this->ingest($content, basename($filePath));
    }

    public function search(string $query, int $limit = 5, float $minScore = 0.3): Collection
    {
        $this->ensureKnowledgeBase();

        $queryEmbedding = $this->generateEmbedding($query);

        $searcher = app(SearchesKnowledge::class);

        return $searcher->search($this->knowledgeBase, $queryEmbedding, $limit, $minScore);
    }

    public function ask(string $question, int $contextChunks = 5): string
    {
        $chunks = $this->search($question, $contextChunks);

        $systemPrompt = $this->buildRagSystemPrompt($chunks);

        $response = $this->manager->text()
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($question)
            ->generate();

        return $response->text;
    }

    public function purge(): self
    {
        $this->ensureKnowledgeBase();

        $this->knowledgeBase->chunks()->delete();

        return $this;
    }

    public function chunkCount(): int
    {
        $this->ensureKnowledgeBase();

        return $this->knowledgeBase->chunks()->count();
    }

    public function getKnowledgeBase(): ?KnowledgeBase
    {
        return $this->knowledgeBase;
    }

    /**
     * @return float[]
     */
    private function generateEmbedding(string $text): array
    {
        $provider = $this->embeddingProvider
            ?? $this->knowledgeBase?->embedding_provider
            ?? config('sidekick.knowledge.embedding.provider', 'openai');

        $model = $this->embeddingModel
            ?? $this->knowledgeBase?->embedding_model
            ?? config('sidekick.knowledge.embedding.model', 'text-embedding-3-small');

        $response = $this->manager->embedding()
            ->using($provider, $model)
            ->withInput($text)
            ->generate();

        return $response->vector();
    }

    private function buildRagSystemPrompt(Collection $chunks): string
    {
        $template = config('sidekick.knowledge.rag_prompt_template');

        if ($template) {
            return str_replace('{{context}}', $this->formatContext($chunks), $template);
        }

        $context = $this->formatContext($chunks);

        return <<<PROMPT
Answer the user's question using ONLY the context provided below. If the answer is not contained in the context, say that you don't have enough information to answer. Do not make up or fabricate any information.

--- Context ---
{$context}
--- End Context ---
PROMPT;
    }

    public function buildWidgetRagPrompt(string $basePrompt, Collection $chunks): string
    {
        $context = $this->formatContext($chunks);

        return <<<PROMPT
{$basePrompt}

The following context was retrieved from the knowledge base and is relevant to the user's question. Prioritize this information over general knowledge when answering. Do not fabricate information that is not in the provided context. If the context doesn't contain relevant information, respond naturally based on your general instructions.

--- Knowledge Base Context ---
{$context}
--- End Context ---
PROMPT;
    }

    private function formatContext(Collection $chunks): string
    {
        return $chunks->map(function ($chunk, $index) {
            $source = $chunk->source ? " (Source: {$chunk->source})" : '';

            return "[".($index + 1)."]".$source."\n".$chunk->content;
        })->implode("\n\n");
    }

    private function ensureKnowledgeBase(): void
    {
        if (! $this->knowledgeBase) {
            throw new \RuntimeException('No knowledge base selected. Call for() first.');
        }
    }
}
