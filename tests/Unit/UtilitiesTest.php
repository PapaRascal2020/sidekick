<?php
namespace PapaRascalDev\Sidekick\Tests\Unit;

use PapaRascalDev\Sidekick\Drivers\OpenAi;
use PapaRascalDev\Sidekick\Features\Completion;
use PapaRascalDev\Sidekick\SidekickDriverInterface;
use PapaRascalDev\Sidekick\Utilities\OpenAiExtras;
use PHPUnit\Framework\TestCase;

class UtilitiesTest extends TestCase
{
    protected SidekickDriverInterface $sidekickMock;
    protected Completion $completionMock;

    protected function setUp(): void
    {
        $this->sidekickMock = $this->createMock(OpenAi::class);
        $this->completionMock = $this->createMock(Completion::class);
    }

    public function testSummarize()
    {
        $content = 'Some long content';
        $expectedResponse = 'summary text';

        $gptUtilitiesMock = $this->createMock(OpenAi::class);
        $gptUtilitiesMock->method('summarize')->willReturn($expectedResponse);

        $this->sidekickMock->method('utilities')->willReturn($gptUtilitiesMock);

        $result = $this->sidekickMock->utilities()->summarize($content);

        $this->assertEquals($expectedResponse, $result);
    }

    public function testExtractKeywords()
    {
        $text = "This is a text with important keywords.";
        $expectedResponse = "text, important, keywords";

        $gptUtilitiesMock = $this->createMock(OpenAi::class);
        $gptUtilitiesMock->method('extractKeywords')->willReturn($expectedResponse);

        $this->sidekickMock->method('utilities')->willReturn($gptUtilitiesMock);

        $result = $this->sidekickMock->utilities()->extractKeywords($text);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testTranslateText()
    {
        $text = "Hello, world!";
        $targetLanguage = "Spanish";
        $expectedResponse = "Hola, mundo!";

        $gptUtilitiesMock = $this->createMock(OpenAi::class);
        $gptUtilitiesMock->method('translateText')->willReturn($expectedResponse);

        $this->sidekickMock->method('utilities')->willReturn($gptUtilitiesMock);

        $result = $this->sidekickMock->utilities()->translateText($text, $targetLanguage);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testGenerateContent()
    {
        $prompt = "Write a story about a brave knight.";
        $expectedResponse = "Once upon a time, there was a brave knight...";

        $gptUtilitiesMock = $this->createMock(OpenAi::class);
        $gptUtilitiesMock->method('generateContent')->willReturn($expectedResponse);

        $this->sidekickMock->method('utilities')->willReturn($gptUtilitiesMock);

        $result = $this->sidekickMock->utilities()->generateContent($prompt);
        $this->assertEquals($expectedResponse, $result);
    }
}
