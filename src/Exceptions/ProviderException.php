<?php

namespace PapaRascalDev\Sidekick\Exceptions;

use Illuminate\Http\Client\Response;

class ProviderException extends SidekickException
{
    public function __construct(
        string $message,
        int $code = 0,
        public readonly ?string $provider = null,
        public readonly ?array $responseBody = null,
    ) {
        parent::__construct($message, $code);
    }

    public static function fromResponse(Response $response, string $provider): self
    {
        $body = $response->json();
        $message = $body['error']['message']
            ?? $body['error']
            ?? $body['message']
            ?? "Provider [{$provider}] returned HTTP {$response->status()}";

        return new self(
            message: $message,
            code: $response->status(),
            provider: $provider,
            responseBody: $body,
        );
    }
}
