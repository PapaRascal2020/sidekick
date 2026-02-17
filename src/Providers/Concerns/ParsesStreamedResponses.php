<?php

namespace PapaRascalDev\Sidekick\Providers\Concerns;

use Generator;
use Illuminate\Support\Facades\Http;
use PapaRascalDev\Sidekick\Exceptions\ProviderException;

trait ParsesStreamedResponses
{
    protected function streamPost(string $url, array $data): Generator
    {
        $response = $this->http()
            ->withOptions(['stream' => true])
            ->post($url, $data);

        if ($response->failed()) {
            throw ProviderException::fromResponse($response, $this->name());
        }

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';

        while (! $body->eof()) {
            $buffer .= $body->read(1024);

            while (($newlinePos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $newlinePos);
                $buffer = substr($buffer, $newlinePos + 1);

                $line = trim($line);

                if ($line === '' || $line === 'data: [DONE]') {
                    continue;
                }

                if (str_starts_with($line, 'data: ')) {
                    $json = substr($line, 6);
                    $decoded = json_decode($json, true);

                    if ($decoded === null) {
                        continue;
                    }

                    $text = $this->extractStreamedText($decoded);

                    if ($text !== null && $text !== '') {
                        yield $text;
                    }
                }
            }
        }
    }

    abstract protected function extractStreamedText(array $data): ?string;
}
