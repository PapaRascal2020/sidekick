<?php

namespace PapaRascalDev\Sidekick\Responses;

use Generator;
use IteratorAggregate;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Traversable;

class StreamResponse implements IteratorAggregate
{
    private ?string $bufferedText = null;

    public function __construct(
        private readonly Generator $generator,
    ) {}

    public function getIterator(): Traversable
    {
        $this->bufferedText = '';

        foreach ($this->generator as $chunk) {
            $this->bufferedText .= $chunk;
            yield $chunk;
        }
    }

    public function text(): string
    {
        if ($this->bufferedText === null) {
            $this->bufferedText = '';
            foreach ($this->generator as $chunk) {
                $this->bufferedText .= $chunk;
            }
        }

        return $this->bufferedText;
    }

    public function toResponse(): StreamedResponse
    {
        return new StreamedResponse(function () {
            foreach ($this as $chunk) {
                echo "data: ".json_encode(['text' => $chunk])."\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
            echo "data: [DONE]\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
