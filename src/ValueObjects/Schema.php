<?php

namespace PapaRascalDev\Sidekick\ValueObjects;

readonly class Schema
{
    /**
     * @param  array<string, mixed>  $schema  A JSON Schema object the response must conform to.
     */
    public function __construct(
        public array $schema,
        public string $name = 'response',
        public bool $strict = true,
    ) {}

    /**
     * @param  array<string, mixed>  $schema
     */
    public static function make(array $schema, string $name = 'response', bool $strict = true): self
    {
        return new self($schema, $name, $strict);
    }
}
