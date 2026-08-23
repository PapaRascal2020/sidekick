<?php

namespace PapaRascalDev\Sidekick\Tests\Feature;

use Illuminate\Support\Facades\Http;
use PapaRascalDev\Sidekick\Exceptions\SidekickException;
use PapaRascalDev\Sidekick\Facades\Sidekick;
use PapaRascalDev\Sidekick\Tests\TestCase;
use PapaRascalDev\Sidekick\ValueObjects\Schema;

class StructuredOutputTest extends TestCase
{
    private function personSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'age' => ['type' => 'integer'],
            ],
            'required' => ['name', 'age'],
            'additionalProperties' => false,
        ];
    }

    public function test_openai_returns_decoded_structured_data(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'id' => 'chatcmpl-1',
                'model' => 'gpt-4o',
                'choices' => [[
                    'finish_reason' => 'stop',
                    'message' => ['role' => 'assistant', 'content' => json_encode(['name' => 'John Doe', 'age' => 42])],
                ]],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
            ]),
        ]);

        $response = Sidekick::text()
            ->using('openai', 'gpt-4o')
            ->withPrompt('Extract the person: John Doe, age 42')
            ->withSchema($this->personSchema())
            ->generate();

        $this->assertTrue($response->hasStructured());
        $this->assertEquals(['name' => 'John Doe', 'age' => 42], $response->structured);

        Http::assertSent(function ($request) {
            $format = $request->data()['response_format'] ?? [];

            return ($format['type'] ?? '') === 'json_schema'
                && ($format['json_schema']['name'] ?? '') === 'response'
                && ($format['json_schema']['strict'] ?? null) === true
                && ($format['json_schema']['schema'] ?? []) === $this->personSchema();
        });
    }

    public function test_openai_accepts_a_schema_object_with_custom_name_and_strict_flag(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'id' => 'chatcmpl-1',
                'model' => 'gpt-4o',
                'choices' => [[
                    'finish_reason' => 'stop',
                    'message' => ['role' => 'assistant', 'content' => json_encode(['name' => 'Jane', 'age' => 30])],
                ]],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
            ]),
        ]);

        Sidekick::text()
            ->using('openai', 'gpt-4o')
            ->withPrompt('Extract the person')
            ->withSchema(Schema::make($this->personSchema(), name: 'person', strict: false))
            ->generate();

        Http::assertSent(function ($request) {
            $format = $request->data()['response_format'] ?? [];

            return ($format['json_schema']['name'] ?? '') === 'person'
                && ($format['json_schema']['strict'] ?? null) === false;
        });
    }

    public function test_anthropic_returns_structured_data_via_forced_tool(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'id' => 'msg_1',
                'model' => 'claude-3-5-sonnet',
                'stop_reason' => 'tool_use',
                'content' => [[
                    'type' => 'tool_use',
                    'id' => 'toolu_1',
                    'name' => 'person',
                    'input' => ['name' => 'John Doe', 'age' => 42],
                ]],
                'usage' => ['input_tokens' => 10, 'output_tokens' => 5],
            ]),
        ]);

        $response = Sidekick::text()
            ->using('anthropic', 'claude-3-5-sonnet')
            ->withPrompt('Extract the person: John Doe, age 42')
            ->withSchema($this->personSchema(), name: 'person')
            ->generate();

        $this->assertTrue($response->hasStructured());
        $this->assertEquals(['name' => 'John Doe', 'age' => 42], $response->structured);
        // The forced schema tool must not leak into the public tool calls.
        $this->assertFalse($response->hasToolCalls());

        Http::assertSent(function ($request) {
            $data = $request->data();

            return ($data['tools'][0]['name'] ?? '') === 'person'
                && ($data['tools'][0]['input_schema'] ?? []) === $this->personSchema()
                && ($data['tool_choice'] ?? []) === ['type' => 'tool', 'name' => 'person'];
        });
    }

    public function test_mistral_returns_decoded_structured_data(): void
    {
        Http::fake([
            'api.mistral.ai/*' => Http::response([
                'id' => 'cmpl-1',
                'model' => 'mistral-large-latest',
                'choices' => [[
                    'finish_reason' => 'stop',
                    'message' => ['role' => 'assistant', 'content' => json_encode(['name' => 'John Doe', 'age' => 42])],
                ]],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
            ]),
        ]);

        $response = Sidekick::text()
            ->using('mistral', 'mistral-large-latest')
            ->withPrompt('Extract the person')
            ->withSchema($this->personSchema())
            ->generate();

        $this->assertEquals(['name' => 'John Doe', 'age' => 42], $response->structured);

        Http::assertSent(fn ($request) => ($request->data()['response_format']['type'] ?? '') === 'json_schema');
    }

    public function test_structured_output_is_null_when_no_schema_requested(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'id' => 'chatcmpl-1',
                'model' => 'gpt-4o',
                'choices' => [['finish_reason' => 'stop', 'message' => ['role' => 'assistant', 'content' => 'Just text']]],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
            ]),
        ]);

        $response = Sidekick::text()
            ->using('openai', 'gpt-4o')
            ->withPrompt('Say hello')
            ->generate();

        $this->assertFalse($response->hasStructured());
        $this->assertNull($response->structured);

        Http::assertSent(fn ($request) => ! isset($request->data()['response_format']));
    }

    public function test_cohere_rejects_structured_output_with_a_clear_exception(): void
    {
        $this->expectException(SidekickException::class);
        $this->expectExceptionMessage('Structured output is not yet supported for the Cohere provider');

        Sidekick::text()
            ->using('cohere', 'command-r')
            ->withPrompt('Extract the person')
            ->withSchema($this->personSchema())
            ->generate();
    }
}
