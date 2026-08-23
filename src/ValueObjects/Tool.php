<?php

namespace PapaRascalDev\Sidekick\ValueObjects;

use Closure;
use PapaRascalDev\Sidekick\Exceptions\SidekickException;

readonly class Tool
{
    public function __construct(
        public string $name,
        public string $description,
        public array $parameters = [],
        public ?Closure $handler = null,
    ) {}

    /**
     * @param  array<string, mixed>  $parameters  A JSON Schema object describing the tool's arguments.
     */
    public static function make(string $name, string $description, array $parameters = [], ?callable $handler = null): self
    {
        return new self(
            name: $name,
            description: $description,
            parameters: $parameters,
            handler: $handler !== null ? Closure::fromCallable($handler) : null,
        );
    }

    public function hasHandler(): bool
    {
        return $this->handler !== null;
    }

    /**
     * The JSON Schema for this tool's arguments, defaulting to an empty object.
     */
    public function schema(): array
    {
        return $this->parameters !== []
            ? $this->parameters
            : ['type' => 'object', 'properties' => (object) []];
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function execute(array $arguments): string
    {
        if ($this->handler === null) {
            throw new SidekickException("Tool [{$this->name}] has no handler to execute.");
        }

        $result = ($this->handler)($arguments);

        return is_string($result) ? $result : (string) json_encode($result);
    }
}
