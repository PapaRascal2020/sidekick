<?php
namespace PapaRascalDev\Sidekick\Tests\Unit;

use PapaRascalDev\Sidekick\Drivers\OpenAi;
use PapaRascalDev\Sidekick\Features\Completion;
use PapaRascalDev\Sidekick\SidekickDriverInterface;
use PapaRascalDev\Sidekick\Utilities\OpenAiExtras;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class UtilitiesTest extends TestCase
{
    protected SidekickDriverInterface $sidekickMock;
    protected Completion $completionMock;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->sidekickMock = $this->createMock(OpenAi::class);
        $this->completionMock = $this->createMock(Completion::class);
    }

    /**
     * @throws Exception
     */
    public function testSummarize()
    {
        $content = 'Some long content';
        $expectedResponse = 'summary text';

        $utilitiesMock = $this->createMock(OpenAiExtras::class);
        $utilitiesMock->method('summarize')->willReturn($expectedResponse);

        $this->sidekickMock->method('utilities')->willReturn($utilitiesMock);

        $result = $this->sidekickMock->utilities()->summarize($content);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * @throws Exception
     */
    public function testExtractKeywords()
    {
        $text = "This is a text with important keywords.";
        $expectedResponse = "text, important, keywords";

        $utilitiesMock = $this->createMock(OpenAiExtras::class);
        $utilitiesMock->method('extractKeywords')->willReturn($expectedResponse);

        $this->sidekickMock->method('utilities')->willReturn($utilitiesMock);

        $result = $this->sidekickMock->utilities()->extractKeywords($text);
        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * @throws Exception
     */
    public function testTranslateText()
    {
        $text = "Hello, world!";
        $targetLanguage = "Spanish";
        $expectedResponse = "Hola, mundo!";

        $utilitiesMock = $this->createMock(OpenAiExtras::class);
        $utilitiesMock->method('translateText')->willReturn($expectedResponse);

        $this->sidekickMock->method('utilities')->willReturn($utilitiesMock);

        $result = $this->sidekickMock->utilities()->translateText($text, $targetLanguage);
        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testGenerateContent()
    {
        $prompt = "Write a story about a brave knight.";
        $expectedResponse = "Once upon a time, there was a brave knight...";

        $utilitiesMock = $this->createMock(OpenAiExtras::class);
        $utilitiesMock->method('generateContent')->willReturn($expectedResponse);

        $this->sidekickMock->method('utilities')->willReturn($utilitiesMock);

        $result = $this->sidekickMock->utilities()->generateContent($prompt);
        $this->assertEquals($expectedResponse, $result);
    }
}
