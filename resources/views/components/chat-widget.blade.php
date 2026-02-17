@props([
    'position' => 'bottom-right',
    'theme' => 'light',
    'title' => 'Chat Assistant',
    'placeholder' => 'Type a message...',
    'buttonLabel' => 'Chat',
])

@php
    $positionClasses = match($position) {
        'bottom-left' => 'bottom: 20px; left: 20px;',
        'top-right' => 'top: 20px; right: 20px;',
        'top-left' => 'top: 20px; left: 20px;',
        default => 'bottom: 20px; right: 20px;',
    };

    $isDark = $theme === 'dark';
@endphp

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<div
    x-data="sidekickChat()"
    style="position: fixed; {{ $positionClasses }} z-index: 9999; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;"
>
    {{-- Toggle Button --}}
    <button
        x-show="!open"
        x-on:click="open = true"
        style="
            padding: 12px 20px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            {{ $isDark ? 'background: #1a1a2e; color: #e0e0e0;' : 'background: #4f46e5; color: white;' }}
        "
    >
        {{ $buttonLabel }}
    </button>

    {{-- Chat Window --}}
    <div
        x-show="open"
        x-transition
        style="
            width: 380px;
            max-height: 520px;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            {{ $isDark ? 'background: #1a1a2e; color: #e0e0e0;' : 'background: #ffffff; color: #1a1a1a;' }}
        "
    >
        {{-- Header --}}
        <div style="
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            {{ $isDark ? 'background: #16213e; border-bottom: 1px solid #2a2a4a;' : 'background: #4f46e5; color: white;' }}
        ">
            <span style="font-weight: 600; font-size: 15px;">{{ $title }}</span>
            <button
                x-on:click="open = false"
                style="background: none; border: none; cursor: pointer; font-size: 18px; {{ $isDark ? 'color: #e0e0e0;' : 'color: white;' }}"
            >&times;</button>
        </div>

        {{-- Messages --}}
        <div
            x-ref="messages"
            style="
                flex: 1;
                overflow-y: auto;
                padding: 16px;
                min-height: 300px;
                max-height: 360px;
                display: flex;
                flex-direction: column;
                gap: 10px;
            "
        >
            <template x-for="(msg, index) in messages" :key="index">
                <div :style="msg.role === 'user'
                    ? 'align-self: flex-end; background: {{ $isDark ? '#2d2d5e' : '#4f46e5' }}; color: {{ $isDark ? '#e0e0e0' : 'white' }}; padding: 8px 14px; border-radius: 16px 16px 4px 16px; max-width: 80%; word-wrap: break-word; font-size: 14px;'
                    : 'align-self: flex-start; background: {{ $isDark ? '#2a2a4a' : '#f0f0f0' }}; color: {{ $isDark ? '#e0e0e0' : '#1a1a1a' }}; padding: 8px 14px; border-radius: 16px 16px 16px 4px; max-width: 80%; word-wrap: break-word; font-size: 14px;'"
                >
                    <template x-if="msg.role === 'user'">
                        <span x-text="msg.content"></span>
                    </template>
                    <template x-if="msg.role !== 'user'">
                        <div class="sidekick-markdown" x-html="renderMarkdown(msg.content)"></div>
                    </template>
                </div>
            </template>
            <div x-show="loading" style="align-self: flex-start; font-size: 13px; opacity: 0.6;">Thinking...</div>
        </div>

        {{-- Input --}}
        <form
            x-on:submit.prevent="send()"
            style="
                display: flex;
                padding: 12px;
                gap: 8px;
                {{ $isDark ? 'border-top: 1px solid #2a2a4a;' : 'border-top: 1px solid #e5e5e5;' }}
            "
        >
            <input
                x-model="input"
                type="text"
                placeholder="{{ $placeholder }}"
                :disabled="loading"
                style="
                    flex: 1;
                    padding: 10px 14px;
                    border-radius: 8px;
                    border: 1px solid {{ $isDark ? '#3a3a5a' : '#d1d5db' }};
                    font-size: 14px;
                    outline: none;
                    {{ $isDark ? 'background: #16213e; color: #e0e0e0;' : 'background: white; color: #1a1a1a;' }}
                "
            />
            <button
                type="submit"
                :disabled="loading || !input.trim()"
                style="
                    padding: 10px 16px;
                    border-radius: 8px;
                    border: none;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 600;
                    {{ $isDark ? 'background: #4f46e5; color: white;' : 'background: #4f46e5; color: white;' }}
                "
            >Send</button>
        </form>
    </div>
</div>

<style>
.sidekick-markdown { line-height: 1.5; }
.sidekick-markdown p { margin: 0 0 0.5em 0; }
.sidekick-markdown p:last-child { margin-bottom: 0; }
.sidekick-markdown ul, .sidekick-markdown ol { margin: 0.25em 0 0.5em 0; padding-left: 1.25em; }
.sidekick-markdown li { margin-bottom: 0.15em; }
.sidekick-markdown code { font-size: 0.85em; background: rgba(0,0,0,0.08); padding: 0.1em 0.35em; border-radius: 3px; }
.sidekick-markdown pre { margin: 0.5em 0; padding: 0.6em 0.8em; border-radius: 6px; background: rgba(0,0,0,0.08); overflow-x: auto; font-size: 0.8em; }
.sidekick-markdown pre code { background: none; padding: 0; }
.sidekick-markdown strong { font-weight: 600; }
.sidekick-markdown h1, .sidekick-markdown h2, .sidekick-markdown h3 { font-weight: 600; margin: 0.5em 0 0.25em 0; }
.sidekick-markdown h1 { font-size: 1.1em; }
.sidekick-markdown h2 { font-size: 1.05em; }
.sidekick-markdown h3 { font-size: 1em; }
.sidekick-markdown a { text-decoration: underline; }
.sidekick-markdown blockquote { border-left: 3px solid rgba(0,0,0,0.15); margin: 0.5em 0; padding-left: 0.75em; opacity: 0.85; }
</style>

<script>
function sidekickChat() {
    return {
        open: false,
        input: '',
        messages: [],
        loading: false,
        conversationId: null,

        renderMarkdown(text) {
            if (!text) return '';
            if (typeof marked !== 'undefined') {
                return marked.parse(text, { breaks: true });
            }
            return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        },

        async send() {
            const text = this.input.trim();
            if (!text || this.loading) return;

            this.messages.push({ role: 'user', content: text });
            this.input = '';
            this.loading = true;
            this.scrollToBottom();

            try {
                const response = await fetch('{{ url(config("sidekick.widget.route_prefix", "sidekick")) }}/chat/message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'text/event-stream',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        message: text,
                        conversation_id: this.conversationId,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                this.messages.push({ role: 'assistant', content: '' });
                const assistantIndex = this.messages.length - 1;

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    const chunk = decoder.decode(value, { stream: true });
                    const lines = chunk.split('\n');

                    for (const line of lines) {
                        if (line.startsWith('data: ')) {
                            const data = line.slice(6);
                            if (data === '[DONE]') continue;

                            try {
                                const parsed = JSON.parse(data);
                                if (parsed.conversation_id) {
                                    this.conversationId = parsed.conversation_id;
                                }
                                if (parsed.text) {
                                    this.messages[assistantIndex].content += parsed.text;
                                    this.scrollToBottom();
                                }
                            } catch (e) {}
                        }
                    }
                }
            } catch (error) {
                this.messages.push({ role: 'assistant', content: 'Sorry, something went wrong. Please try again.' });
            } finally {
                this.loading = false;
                this.scrollToBottom();
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messages;
                if (el) el.scrollTop = el.scrollHeight;
            });
        }
    };
}
</script>
