<?php

namespace PapaRascalDev\Sidekick\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PapaRascalDev\Sidekick\Builders\KnowledgeBuilder;
use PapaRascalDev\Sidekick\Contracts\SearchesKnowledge;
use PapaRascalDev\Sidekick\Knowledge\VectorSearch;
use PapaRascalDev\Sidekick\Models\KnowledgeBase;
use PapaRascalDev\Sidekick\Models\KnowledgeChunk;
use PapaRascalDev\Sidekick\Responses\EmbeddingResponse;
use PapaRascalDev\Sidekick\SidekickManager;
use PapaRascalDev\Sidekick\Tests\TestCase;
use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

class KnowledgeBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_creates_knowledge_base(): void
    {
        $manager = app('sidekick');
        $builder = $manager->knowledge('test-kb');

        $kb = $builder->getKnowledgeBase();

        $this->assertNotNull($kb);
        $this->assertEquals('test-kb', $kb->name);
        $this->assertDatabaseHas('sidekick_knowledge_bases', ['name' => 'test-kb']);
    }

    public function test_for_reuses_existing_knowledge_base(): void
    {
        $manager = app('sidekick');

        $builder1 = $manager->knowledge('test-kb');
        $builder2 = $manager->knowledge('test-kb');

        $this->assertEquals(
            $builder1->getKnowledgeBase()->id,
            $builder2->getKnowledgeBase()->id
        );

        $this->assertDatabaseCount('sidekick_knowledge_bases', 1);
    }

    public function test_ingest_creates_chunks(): void
    {
        $this->mockEmbedding([0.1, 0.2, 0.3]);

        $manager = app('sidekick');
        $builder = $manager->knowledge('test-kb');

        $builder->ingest('This is test content.', 'test-source');

        $this->assertDatabaseHas('sidekick_knowledge_chunks', [
            'knowledge_base_id' => $builder->getKnowledgeBase()->id,
            'content' => 'This is test content.',
            'source' => 'test-source',
            'chunk_index' => 0,
        ]);

        $this->assertEquals(1, $builder->chunkCount());
    }

    public function test_ingest_long_text_creates_multiple_chunks(): void
    {
        $this->mockEmbedding([0.1, 0.2, 0.3]);

        // Create a KB with small chunk size for testing
        $kb = KnowledgeBase::create([
            'name' => 'small-chunks',
            'chunk_size' => 50,
            'chunk_overlap' => 10,
        ]);

        $manager = app('sidekick');
        $builder = $manager->knowledge('small-chunks');

        $text = str_repeat('This is a test sentence. ', 10); // ~250 chars
        $builder->ingest($text, 'test');

        $this->assertGreaterThan(1, $builder->chunkCount());
    }

    public function test_search_returns_ranked_results(): void
    {
        // Create the KB directly so we can add chunks with known embeddings
        $kb = KnowledgeBase::create([
            'name' => 'search-test',
            'embedding_provider' => 'openai',
            'embedding_model' => 'text-embedding-3-small',
        ]);

        $kb->chunks()->create([
            'content' => 'Very relevant content',
            'embedding' => [1.0, 0.0, 0.0],
            'source' => 'test',
            'chunk_index' => 0,
        ]);

        $kb->chunks()->create([
            'content' => 'Somewhat relevant content',
            'embedding' => [0.7, 0.7, 0.0],
            'source' => 'test',
            'chunk_index' => 1,
        ]);

        $kb->chunks()->create([
            'content' => 'Irrelevant content',
            'embedding' => [0.0, 0.0, 1.0],
            'source' => 'test',
            'chunk_index' => 2,
        ]);

        // Mock embedding so search() gets a known query vector
        $this->mockEmbedding([1.0, 0.0, 0.0]);

        $builder = app('sidekick')->knowledge('search-test');
        $results = $builder->search('test query', 5, 0.3);

        $this->assertGreaterThanOrEqual(1, $results->count());
        $this->assertEquals('Very relevant content', $results->first()->content);
    }

    public function test_purge_clears_chunks(): void
    {
        $this->mockEmbedding([0.1, 0.2, 0.3]);

        $manager = app('sidekick');
        $builder = $manager->knowledge('purge-test');

        $builder->ingest('Some content.', 'test');
        $this->assertGreaterThan(0, $builder->chunkCount());

        $builder->purge();
        $this->assertEquals(0, $builder->chunkCount());
    }

    public function test_knowledge_base_has_chunks_relationship(): void
    {
        $this->mockEmbedding([0.1, 0.2, 0.3]);

        $manager = app('sidekick');
        $builder = $manager->knowledge('cascade-test');

        $builder->ingest('Test content.', 'test');
        $kb = $builder->getKnowledgeBase();

        $this->assertEquals(1, $kb->chunks()->count());
        $this->assertEquals('Test content.', $kb->chunks->first()->content);

        // Verify chunks belong to the correct knowledge base
        $chunk = $kb->chunks->first();
        $this->assertEquals($kb->id, $chunk->knowledgeBase->id);
    }

    public function test_using_sets_embedding_provider(): void
    {
        $manager = app('sidekick');
        $builder = (new KnowledgeBuilder($manager))->using('anthropic', 'custom-model')->for('provider-test');

        $kb = $builder->getKnowledgeBase();

        $this->assertEquals('anthropic', $kb->embedding_provider);
        $this->assertEquals('custom-model', $kb->embedding_model);
    }

    public function test_vector_search_is_bound_in_container(): void
    {
        $searcher = app(SearchesKnowledge::class);

        $this->assertInstanceOf(VectorSearch::class, $searcher);
    }

    private function mockEmbedding(array $vector): void
    {
        $manager = app('sidekick');
        $fakeVector = $vector;

        $fake = new class($manager, $fakeVector) extends SidekickManager {
            private array $fakeVector;

            public function __construct(SidekickManager $real, array $fakeVector)
            {
                parent::__construct($real->container);
                $this->fakeVector = $fakeVector;
            }

            public function embedding(): \PapaRascalDev\Sidekick\Builders\EmbeddingBuilder
            {
                $vector = $this->fakeVector;

                return new class($this, $vector) extends \PapaRascalDev\Sidekick\Builders\EmbeddingBuilder {
                    private array $fakeVector;

                    public function __construct(SidekickManager $manager, array $fakeVector)
                    {
                        parent::__construct($manager);
                        $this->fakeVector = $fakeVector;
                    }

                    public function generate(): EmbeddingResponse
                    {
                        return new EmbeddingResponse(
                            embeddings: [$this->fakeVector],
                            usage: new Usage(10, 0, 10),
                            meta: new Meta('openai', 'text-embedding-3-small'),
                        );
                    }
                };
            }
        };

        app()->instance('sidekick', $fake);
    }
}
