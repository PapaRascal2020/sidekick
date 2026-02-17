<?php

namespace PapaRascalDev\Sidekick\Tests\Unit;

use PapaRascalDev\Sidekick\Knowledge\VectorSearch;
use PHPUnit\Framework\TestCase;

class VectorSearchTest extends TestCase
{
    private VectorSearch $search;

    protected function setUp(): void
    {
        parent::setUp();
        $this->search = new VectorSearch;
    }

    public function test_identical_vectors_return_one(): void
    {
        $vector = [1.0, 0.0, 0.0];

        $similarity = $this->search->cosineSimilarity($vector, $vector);

        $this->assertEqualsWithDelta(1.0, $similarity, 0.0001);
    }

    public function test_orthogonal_vectors_return_zero(): void
    {
        $a = [1.0, 0.0, 0.0];
        $b = [0.0, 1.0, 0.0];

        $similarity = $this->search->cosineSimilarity($a, $b);

        $this->assertEqualsWithDelta(0.0, $similarity, 0.0001);
    }

    public function test_opposite_vectors_return_negative_one(): void
    {
        $a = [1.0, 0.0, 0.0];
        $b = [-1.0, 0.0, 0.0];

        $similarity = $this->search->cosineSimilarity($a, $b);

        $this->assertEqualsWithDelta(-1.0, $similarity, 0.0001);
    }

    public function test_empty_vectors_return_zero(): void
    {
        $similarity = $this->search->cosineSimilarity([], []);

        $this->assertEquals(0.0, $similarity);
    }

    public function test_mismatched_lengths_return_zero(): void
    {
        $a = [1.0, 0.0];
        $b = [1.0, 0.0, 0.0];

        $similarity = $this->search->cosineSimilarity($a, $b);

        $this->assertEquals(0.0, $similarity);
    }

    public function test_zero_vectors_return_zero(): void
    {
        $a = [0.0, 0.0, 0.0];
        $b = [0.0, 0.0, 0.0];

        $similarity = $this->search->cosineSimilarity($a, $b);

        $this->assertEquals(0.0, $similarity);
    }

    public function test_similar_vectors_return_high_score(): void
    {
        $a = [1.0, 0.5, 0.3];
        $b = [0.9, 0.6, 0.2];

        $similarity = $this->search->cosineSimilarity($a, $b);

        $this->assertGreaterThan(0.95, $similarity);
    }
}
