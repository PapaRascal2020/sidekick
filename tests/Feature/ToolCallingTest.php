<?php

namespace PapaRascalDev\Sidekick\Tests\Feature;

use Illuminate\Support\Facades\Http;
use PapaRascalDev\Sidekick\Exceptions\SidekickException;
use PapaRascalDev\Sidekick\Facades\Sidekick;
use PapaRascalDev\Sidekick\Tests\TestCase;
use PapaRascalDev\Sidekick\ValueObjects\Tool;

class ToolCallingTest extends TestCase
{
    private function weatherTool(): Tool
    {
        return Tool::make(
            name: 'get_weather',
            description: 'Get the current weather for a city.',
            parameters: [
                'type' => 'object',
                'properties' => ['city' => ['type' => 'string']],
                'required' => ['city'],
            ],
            handler: fn (array $args) => 'Sunny, 22C',
        );
    }

    public function test_openai_runs_the_tool_and_returns_the_final_answer(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::sequence()
                ->push([
                    'id' => 'chatcmpl-1',
                    'model' => 'gpt-4o',
                    'choices' => [[
                        'finish_reason' => 'tool_calls',
                        'message' => [
                            'role' => 'assistant',
                            'content' => null,
                            'tool_calls' => [[
                                'id' => 'call_1',
                                'type' => 'function',
                                'function' => ['name' => 'get_weather', 'arguments' => '{"city":"London"}'],
                            ]],
                        ],
                    ]],
                    'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
                ])
                ->push([
                    'id' => 'chatcmpl-2',
                    'model' => 'gpt-4o',
                    'choices' => [[
                        'finish_reason' => 'stop',
                        'message' => ['role' => 'assistant', 'content' => 'It is Sunny, 22C in London.'],
                    ]],
                    'usage' => ['prompt_tokens' => 20, 'completion_tokens' => 8, 'total_tokens' => 28],
                ]),
        ]);

        $response = Sidekick::text()
            ->using('openai', 'gpt-4o')
            ->withPrompt('What is the weather in London?')
            ->withTools([$this->weatherTool()])
            ->generate();

        $this->assertEquals('It is Sunny, 22C in London.', $response->text);
        $this->assertFalse($response->hasToolCalls());

        Http::assertSentCount(2);

        // The second request must carry the assistant tool call and the tool result.
        Http::assertSent(function ($request) {
            $messages = $request->data()['messages'] ?? [];
            $roles = array_column($messages, 'role');

            return in_array('tool', $roles, true)
                && collect($messages)->contains(fn ($m) => ($m['role'] ?? '') === 'tool' && ($m['content'] ?? '') === 'Sunny, 22C');
        });
    }

    public function test_openai_returns_tool_calls_for_manual_handling_when_no_handler(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'id' => 'chatcmpl-1',
                'model' => 'gpt-4o',
                'choices' => [[
                    'finish_reason' => 'tool_calls',
                    'message' => [
                        'role' => 'assistant',
                        'content' => null,
                        'tool_calls' => [[
                            'id' => 'call_1',
                            'type' => 'function',
                            'function' => ['name' => 'get_weather', 'arguments' => '{"city":"London"}'],
                        ]],
                    ],
                ]],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
            ]),
        ]);

        $response = Sidekick::text()
            ->using('openai', 'gpt-4o')
            ->withPrompt('What is the weather in London?')
            ->withTools([
                Tool::make('get_weather', 'Get the current weather for a city.'),
            ])
            ->generate();

        $this->assertTrue($response->hasToolCalls());
        $this->assertCount(1, $response->toolCalls);
        $this->assertEquals('get_weather', $response->toolCalls[0]->name);
        $this->assertEquals(['city' => 'London'], $response->toolCalls[0]->arguments);
        $this->assertEquals('call_1', $response->toolCalls[0]->id);

        Http::assertSentCount(1);
    }

    public function test_anthropic_runs_the_tool_and_returns_the_final_answer(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::sequence()
                ->push([
                    'id' => 'msg_1',
                    'model' => 'claude-3-5-sonnet',
                    'stop_reason' => 'tool_use',
                    'content' => [
                        ['type' => 'text', 'text' => 'Let me check.'],
                        ['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'get_weather', 'input' => ['city' => 'London']],
                    ],
                    'usage' => ['input_tokens' => 10, 'output_tokens' => 5],
                ])
                ->push([
                    'id' => 'msg_2',
                    'model' => 'claude-3-5-sonnet',
                    'stop_reason' => 'end_turn',
                    'content' => [['type' => 'text', 'text' => 'It is Sunny, 22C in London.']],
                    'usage' => ['input_tokens' => 20, 'output_tokens' => 8],
                ]),
        ]);

        $response = Sidekick::text()
            ->using('anthropic', 'claude-3-5-sonnet')
            ->withPrompt('What is the weather in London?')
            ->withTools([$this->weatherTool()])
            ->generate();

        $this->assertEquals('It is Sunny, 22C in London.', $response->text);
        $this->assertFalse($response->hasToolCalls());

        Http::assertSentCount(2);

        Http::assertSent(function ($request) {
            $messages = $request->data()['messages'] ?? [];

            foreach ($messages as $message) {
                if (! is_array($message['content'] ?? null)) {
                    continue;
                }

                foreach ($message['content'] as $block) {
                    if (is_array($block) && ($block['type'] ?? '') === 'tool_result' && ($block['tool_use_id'] ?? '') === 'toolu_1') {
                        return $block['content'] === 'Sunny, 22C';
                    }
                }
            }

            return false;
        });
    }

    public function test_tool_definition_is_sent_in_the_first_request(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'id' => 'chatcmpl-1',
                'model' => 'gpt-4o',
                'choices' => [['finish_reason' => 'stop', 'message' => ['role' => 'assistant', 'content' => 'Hi']]],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
            ]),
        ]);

        Sidekick::text()
            ->using('openai', 'gpt-4o')
            ->withPrompt('Hello')
            ->withTools([$this->weatherTool()])
            ->generate();

        Http::assertSent(function ($request) {
            $tools = $request->data()['tools'] ?? [];

            return count($tools) === 1
                && $tools[0]['type'] === 'function'
                && $tools[0]['function']['name'] === 'get_weather';
        });
    }

    public function test_max_tool_calls_stops_the_loop(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'id' => 'chatcmpl-1',
                'model' => 'gpt-4o',
                'choices' => [[
                    'finish_reason' => 'tool_calls',
                    'message' => [
                        'role' => 'assistant',
                        'content' => null,
                        'tool_calls' => [[
                            'id' => 'call_1',
                            'type' => 'function',
                            'function' => ['name' => 'get_weather', 'arguments' => '{"city":"London"}'],
                        ]],
                    ],
                ]],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
            ]),
        ]);

        $response = Sidekick::text()
            ->using('openai', 'gpt-4o')
            ->withPrompt('What is the weather in London?')
            ->withTools([$this->weatherTool()])
            ->withMaxToolCalls(1)
            ->generate();

        // One execution round runs, then the loop stops on the second tool_calls response.
        $this->assertTrue($response->hasToolCalls());
        Http::assertSentCount(2);
    }

    public function test_cohere_rejects_tools_with_a_clear_exception(): void
    {
        $this->expectException(SidekickException::class);
        $this->expectExceptionMessage('Tool calling is not yet supported for the Cohere provider');

        Sidekick::text()
            ->using('cohere', 'command-r')
            ->withPrompt('What is the weather in London?')
            ->withTools([$this->weatherTool()])
            ->generate();
    }
}
