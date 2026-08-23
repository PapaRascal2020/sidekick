<?php

namespace PapaRascalDev\Sidekick\Tests\Unit;

use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\Tests\TestCase;
use PapaRascalDev\Sidekick\ValueObjects\Meta;
use PapaRascalDev\Sidekick\ValueObjects\Schema;
use PapaRascalDev\Sidekick\ValueObjects\Usage;

class SchemaTest extends TestCase
{
    public function test_schema_defaults(): void
    {
        $schema = Schema::make(['type' => 'object']);

        $this->assertEquals(['type' => 'object'], $schema->schema);
        $this->assertEquals('response', $schema->name);
        $this->assertTrue($schema->strict);
    }

    public function test_schema_custom_name_and_strict(): void
    {
        $schema = Schema::make(['type' => 'object'], name: 'person', strict: false);

        $this->assertEquals('person', $schema->name);
        $this->assertFalse($schema->strict);
    }

    public function test_text_response_structured_accessors(): void
    {
        $withData = new TextResponse(
            text: '',
            usage: new Usage(),
            meta: new Meta('openai', 'gpt-4o'),
            structured: ['name' => 'John'],
        );

        $this->assertTrue($withData->hasStructured());
        $this->assertEquals(['name' => 'John'], $withData->structured);

        $withoutData = new TextResponse(text: 'hi', usage: new Usage(), meta: new Meta('openai', 'gpt-4o'));

        $this->assertFalse($withoutData->hasStructured());
        $this->assertNull($withoutData->structured);
    }
}
