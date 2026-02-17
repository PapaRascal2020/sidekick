<?php

namespace PapaRascalDev\Sidekick\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PapaRascalDev\Sidekick\Builders\KnowledgeBuilder;
use PapaRascalDev\Sidekick\Enums\Role;
use PapaRascalDev\Sidekick\Models\Conversation;
use PapaRascalDev\Sidekick\Models\ConversationMessage;
use PapaRascalDev\Sidekick\ValueObjects\Message;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatWidgetController extends Controller
{
    public function message(Request $request): StreamedResponse
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'conversation_id' => 'nullable|string',
        ]);

        $provider = config('sidekick.widget.provider', 'openai');
        $model = config('sidekick.widget.model', 'gpt-4o');
        $systemPrompt = config('sidekick.widget.system_prompt', 'You are a helpful assistant.');
        $maxTokens = config('sidekick.widget.max_tokens', 1024);
        $knowledgeBaseName = config('sidekick.widget.knowledge_base');

        // Augment system prompt with RAG context if a knowledge base is configured
        if ($knowledgeBaseName) {
            $systemPrompt = $this->augmentWithKnowledge(
                $request->input('message'),
                $knowledgeBaseName,
                $systemPrompt,
            );
        }

        $conversation = $request->input('conversation_id')
            ? Conversation::find($request->input('conversation_id'))
            : null;

        if (! $conversation) {
            $conversation = Conversation::create([
                'provider' => $provider,
                'model' => $model,
                'system_prompt' => $systemPrompt,
                'max_tokens' => $maxTokens,
            ]);
        }

        // Store user message
        $conversation->messages()->create([
            'role' => Role::User->value,
            'content' => $request->input('message'),
        ]);

        // Build message history
        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn (ConversationMessage $msg) => new Message(Role::from($msg->role), $msg->content))
            ->all();

        $manager = app('sidekick');
        $providerInstance = $manager->provider($provider);
        $generator = $providerInstance->streamText(
            model: $model,
            messages: $messages,
            systemPrompt: $systemPrompt,
            maxTokens: $maxTokens,
        );

        return new StreamedResponse(function () use ($generator, $conversation) {
            echo "data: ".json_encode(['conversation_id' => $conversation->id])."\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            $fullText = '';

            foreach ($generator as $chunk) {
                $fullText .= $chunk;
                echo "data: ".json_encode(['text' => $chunk])."\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }

            // Store assistant response
            $conversation->messages()->create([
                'role' => Role::Assistant->value,
                'content' => $fullText,
            ]);

            echo "data: [DONE]\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function augmentWithKnowledge(string $userMessage, string $kbName, string $basePrompt): string
    {
        try {
            $manager = app('sidekick');
            $builder = $manager->knowledge($kbName);

            $contextChunks = config('sidekick.widget.rag_context_chunks', 5);
            $minScore = config('sidekick.widget.rag_min_score', 0.3);

            $chunks = $builder->search($userMessage, $contextChunks, $minScore);

            if ($chunks->isEmpty()) {
                return $basePrompt;
            }

            return $builder->buildWidgetRagPrompt($basePrompt, $chunks);
        } catch (\Throwable $e) {
            report($e);

            return $basePrompt;
        }
    }
}
