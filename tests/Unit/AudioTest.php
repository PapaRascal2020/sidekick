<?php

use PapaRascalDev\Sidekick\Features\Audio;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class AudioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_you_can_generate_audio_from_text()
    {
        $model = 'tts-1';
        $text = 'Hello, world!';

        $mockedAudio = $this->createMock(Audio::class);

        // Create a fake binary file
        $file = UploadedFile::fake()->create('audio.mp3', 100, 'audio/mpeg');


        $mockedAudio->method('fromText')->willReturn(file_get_contents($file));

        $response = $mockedAudio->fromText($model, $text);

        $this->assertStringEqualsFile($file, $response);
    }
}
