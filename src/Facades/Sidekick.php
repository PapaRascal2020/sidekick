<?php

namespace PapaRascalDev\Sidekick\Facades;

use Illuminate\Support\Facades\Facade;
use PapaRascalDev\Sidekick\Builders\AudioBuilder;
use PapaRascalDev\Sidekick\Builders\ConversationBuilder;
use PapaRascalDev\Sidekick\Builders\EmbeddingBuilder;
use PapaRascalDev\Sidekick\Builders\ImageBuilder;
use PapaRascalDev\Sidekick\Builders\KnowledgeBuilder;
use PapaRascalDev\Sidekick\Builders\ModerationBuilder;
use PapaRascalDev\Sidekick\Builders\TextBuilder;
use PapaRascalDev\Sidekick\Builders\TranscriptionBuilder;
use PapaRascalDev\Sidekick\Testing\SidekickFake;

/**
 * @method static TextBuilder text()
 * @method static ImageBuilder image()
 * @method static AudioBuilder audio()
 * @method static TranscriptionBuilder transcription()
 * @method static EmbeddingBuilder embedding()
 * @method static ModerationBuilder moderation()
 * @method static ConversationBuilder conversation()
 * @method static KnowledgeBuilder knowledge(string $name)
 * @method static string summarize(string $content, int $maxLength = 500)
 * @method static string translate(string $text, string $targetLanguage)
 * @method static string extractKeywords(string $text)
 * @method static SidekickFake fake(array $responses = [])
 * @method static \PapaRascalDev\Sidekick\SidekickManager registerProvider(string $name, callable $resolver)
 *
 * @see \PapaRascalDev\Sidekick\SidekickManager
 */
class Sidekick extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sidekick';
    }
}
