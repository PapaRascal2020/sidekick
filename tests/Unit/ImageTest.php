<?php

use PapaRascalDev\Sidekick\Features\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_you_can_generate_image_from_text()
    {
        $model = 'dall-e-2';
        $text = 'A cat';

        $response = "http://example.com/image.jpg";

        $mockedImage = $this->createMock(Image::class);

        $mockedImage->method('generate')->willReturn([
                'data' => [
                    0 => [
                        'url' => 'http://example.com/image.jpg'
                    ]
                ]
        ]);


        $result = $mockedImage->generate($model, $text);

        $this->assertEquals($response, $result['data'][0]['url']);
    }
}
