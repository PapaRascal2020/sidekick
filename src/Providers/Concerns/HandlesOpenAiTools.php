<?php

namespace PapaRascalDev\Sidekick\Providers\Concerns;

use PapaRascalDev\Sidekick\Responses\TextResponse;
use PapaRascalDev\Sidekick\ValueObjects\Tool;
use PapaRascalDev\Sidekick\ValueObjects\ToolCall;

/**
 * Shared tool-calling wire format for OpenAI-compatible chat APIs (OpenAI, Mistral).
 */
trait HandlesOpenAiTools
{
    /**
     * @param  Tool[]  $tools
     * @return array<int, array<string, mixed>>
     */
    protected function formatTools(array $tools): array
    {
        return array_map(fn (Tool $tool) => [
            'type' => 'function',
            'function' => [
                'name' => $tool->name,
                'description' => $tool->description,
                'parameters' => $tool->schema(),
            ],
        ], $tools);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return ToolCall[]
     */
    protected function parseToolCalls(array $data): array
    {
        $toolCalls = [];

        foreach ($data['choices'][0]['message']['tool_calls'] ?? [] as $call) {
            $arguments = $call['function']['arguments'] ?? [];

            $toolCalls[] = new ToolCall(
                name: $call['function']['name'] ?? '',
                arguments: is_array($arguments) ? $arguments : (json_decode($arguments, true) ?? []),
                id: $call['id'] ?? null,
            );
        }

        return $toolCalls;
    }

    /**
     * @param  array<int, array{id: ?string, name: string, output: string}>  $results
     * @return array<int, array<string, mixed>>
     */
    protected function openAiToolResultMessages(TextResponse $response, array $results, bool $includeName = false): array
    {
        $toolCalls = array_map(fn (ToolCall $call) => [
            'id' => $call->id,
            'type' => 'function',
            'function' => [
                'name' => $call->name,
                'arguments' => (string) json_encode($call->arguments === [] ? (object) [] : $call->arguments),
            ],
        ], $response->toolCalls);

        $messages = [[
            'role' => 'assistant',
            'content' => $response->text !== '' ? $response->text : null,
            'tool_calls' => $toolCalls,
        ]];

        foreach ($results as $result) {
            $message = [
                'role' => 'tool',
                'tool_call_id' => $result['id'],
                'content' => $result['output'],
            ];

            if ($includeName) {
                $message['name'] = $result['name'];
            }

            $messages[] = $message;
        }

        return $messages;
    }
}
