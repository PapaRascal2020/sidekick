<?php

namespace PapaRascalDev\Sidekick\Tests\Unit;

use PapaRascalDev\Sidekick\Exceptions\SidekickException;
use PapaRascalDev\Sidekick\Tests\TestCase;
use PapaRascalDev\Sidekick\ValueObjects\Tool;
use PapaRascalDev\Sidekick\ValueObjects\ToolCall;

class ToolTest extends TestCase
{
    public function test_tool_make_builds_a_tool(): void
    {
        $tool = Tool::make(
            name: 'get_weather',
            description: 'Get the weather.',
            parameters: ['type' => 'object', 'properties' => ['city' => ['type' => 'string']]],
            handler: fn (array $args) => 'Sunny',
        );

        $this->assertEquals('get_weather', $tool->name);
        $this->assertEquals('Get the weather.', $tool->description);
        $this->assertTrue($tool->hasHandler());
        $this->assertEquals('Sunny', $tool->execute(['city' => 'London']));
    }

    public function test_tool_without_handler_reports_and_throws_on_execute(): void
    {
        $tool = Tool::make('get_weather', 'Get the weather.');

        $this->assertFalse($tool->hasHandler());

        $this->expectException(SidekickException::class);
        $tool->execute([]);
    }

    public function test_tool_execute_json_encodes_non_string_results(): void
    {
        $tool = Tool::make('lookup', 'Lookup', handler: fn (array $args) => ['temp' => 22]);

        $this->assertEquals('{"temp":22}', $tool->execute([]));
    }

    public function test_tool_schema_defaults_to_empty_object(): void
    {
        $tool = Tool::make('noop', 'No arguments.');

        $schema = $tool->schema();

        $this->assertEquals('object', $schema['type']);
        $this->assertEquals([], (array) $schema['properties']);
    }

    public function test_tool_call_to_array(): void
    {
        $call = new ToolCall(name: 'get_weather', arguments: ['city' => 'London'], id: 'call_1');

        $this->assertEquals([
            'id' => 'call_1',
            'name' => 'get_weather',
            'arguments' => ['city' => 'London'],
        ], $call->toArray());
    }
}
