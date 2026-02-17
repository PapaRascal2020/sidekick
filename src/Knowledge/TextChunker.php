<?php

namespace PapaRascalDev\Sidekick\Knowledge;

class TextChunker
{
    public function __construct(
        private readonly int $chunkSize = 2000,
        private readonly int $overlap = 200,
    ) {}

    /**
     * @return string[]
     */
    public function chunk(string $text): array
    {
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        if (mb_strlen($text) <= $this->chunkSize) {
            return [$text];
        }

        $chunks = [];
        $offset = 0;
        $length = mb_strlen($text);

        while ($offset < $length) {
            $end = min($offset + $this->chunkSize, $length);
            $chunk = mb_substr($text, $offset, $end - $offset);

            // If we haven't reached the end, try to break at a sentence boundary
            if ($end < $length) {
                $chunk = $this->breakAtBoundary($chunk);
            }

            $chunks[] = trim($chunk);

            $chunkLength = mb_strlen($chunk);

            // Move forward by chunk length minus overlap
            $advance = max(1, $chunkLength - $this->overlap);
            $offset += $advance;
        }

        return array_values(array_filter($chunks, fn (string $c) => $c !== ''));
    }

    private function breakAtBoundary(string $chunk): string
    {
        // Try to break at sentence boundary (.!? followed by space or end)
        $sentenceBreak = $this->findLastSentenceBreak($chunk);
        if ($sentenceBreak !== false) {
            return mb_substr($chunk, 0, $sentenceBreak);
        }

        // Fall back to paragraph boundary (double newline)
        $paragraphBreak = mb_strrpos($chunk, "\n\n");
        if ($paragraphBreak !== false && $paragraphBreak > $this->chunkSize * 0.3) {
            return mb_substr($chunk, 0, $paragraphBreak);
        }

        // Fall back to word boundary (space)
        $wordBreak = mb_strrpos($chunk, ' ');
        if ($wordBreak !== false && $wordBreak > $this->chunkSize * 0.3) {
            return mb_substr($chunk, 0, $wordBreak);
        }

        // Hard cut as last resort
        return $chunk;
    }

    private function findLastSentenceBreak(string $chunk): int|false
    {
        $lastBreak = false;
        $length = mb_strlen($chunk);

        // Look for .!? followed by whitespace, working backwards from the end
        // Only consider breaks in the last 70% of the chunk to avoid too-small chunks
        $minPosition = (int) ($this->chunkSize * 0.3);

        for ($i = $length - 1; $i >= $minPosition; $i--) {
            $char = mb_substr($chunk, $i, 1);

            if (in_array($char, ['.', '!', '?'], true)) {
                // Check if followed by space/newline or is at end
                if ($i + 1 >= $length) {
                    return $i + 1;
                }

                $nextChar = mb_substr($chunk, $i + 1, 1);
                if ($nextChar === ' ' || $nextChar === "\n" || $nextChar === "\r") {
                    return $i + 1;
                }
            }
        }

        return $lastBreak;
    }
}
