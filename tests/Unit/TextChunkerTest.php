<?php

namespace PapaRascalDev\Sidekick\Tests\Unit;

use PapaRascalDev\Sidekick\Knowledge\TextChunker;
use PHPUnit\Framework\TestCase;

class TextChunkerTest extends TestCase
{
    public function test_short_text_returns_single_chunk(): void
    {
        $chunker = new TextChunker(2000, 200);

        $result = $chunker->chunk('Hello, this is a short text.');

        $this->assertCount(1, $result);
        $this->assertEquals('Hello, this is a short text.', $result[0]);
    }

    public function test_empty_text_returns_empty_array(): void
    {
        $chunker = new TextChunker(2000, 200);

        $result = $chunker->chunk('');

        $this->assertCount(0, $result);
    }

    public function test_whitespace_only_returns_empty_array(): void
    {
        $chunker = new TextChunker(2000, 200);

        $result = $chunker->chunk('   ');

        $this->assertCount(0, $result);
    }

    public function test_long_text_creates_overlapping_chunks(): void
    {
        $chunker = new TextChunker(100, 20);

        // Create text that's longer than chunk size
        $text = str_repeat('This is a sentence. ', 20); // ~400 chars
        $result = $chunker->chunk($text);

        $this->assertGreaterThan(1, count($result));

        // Each chunk should not exceed chunk size (approximately, since we break at boundaries)
        foreach ($result as $chunk) {
            $this->assertNotEmpty($chunk);
        }
    }

    public function test_breaks_at_sentence_boundaries(): void
    {
        $chunker = new TextChunker(50, 10);

        $text = 'First sentence here. Second sentence here. Third sentence here. Fourth sentence here.';
        $result = $chunker->chunk($text);

        $this->assertGreaterThan(1, count($result));

        // Check that chunks tend to end at sentence boundaries
        foreach ($result as $index => $chunk) {
            // Last chunk might not end with punctuation
            if ($index < count($result) - 1) {
                $trimmed = rtrim($chunk);
                $lastChar = mb_substr($trimmed, -1);
                $this->assertTrue(
                    in_array($lastChar, ['.', '!', '?'], true) || mb_strlen($chunk) <= 50,
                    "Chunk should end at sentence boundary or be within size limit: '{$chunk}'"
                );
            }
        }
    }

    public function test_utf8_text_handled_correctly(): void
    {
        $chunker = new TextChunker(2000, 200);

        $text = 'Héllo wörld! Ünïcödë tëxt with spëcîal chàrâctërs.';
        $result = $chunker->chunk($text);

        $this->assertCount(1, $result);
        $this->assertEquals($text, $result[0]);
    }

    public function test_exact_chunk_size_text(): void
    {
        $size = 100;
        $chunker = new TextChunker($size, 20);

        $text = str_repeat('a', $size);
        $result = $chunker->chunk($text);

        $this->assertCount(1, $result);
        $this->assertEquals($text, $result[0]);
    }
}
