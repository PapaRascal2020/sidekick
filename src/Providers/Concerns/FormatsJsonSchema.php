<?php

namespace PapaRascalDev\Sidekick\Providers\Concerns;

use PapaRascalDev\Sidekick\ValueObjects\Schema;

/**
 * Shared JSON Schema response formatting for OpenAI-compatible chat APIs (OpenAI, Mistral).
 */
trait FormatsJsonSchema
{
    /**
     * @return array<string, mixed>
     */
    protected function jsonSchemaResponseFormat(Schema $schema): array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => $schema->name,
                'schema' => $schema->schema,
                'strict' => $schema->strict,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function decodeStructured(?string $content): ?array
    {
        if ($content === null || $content === '') {
            return null;
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : null;
    }
}
