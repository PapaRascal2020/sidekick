<?php

namespace PapaRascalDev\Sidekick\Tests\Unit;

use Illuminate\Http\Client\ConnectionException;
use PapaRascalDev\Sidekick\Features\Image;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{

    /**
     * @throws Exception
     * @throws ConnectionException
     */
    public function test_you_can_generate_image_from_text()
    {
        $model = 'dall-e-2';
        $text = 'A cat';

        $response = "https://example.com/image.jpg";

        $mockedImage = $this->createMock(Image::class);

        $mockedImage->method('generate')->willReturn([
                'data' => [
                    0 => [
                        'url' => 'https://example.com/image.jpg'
                    ]
                ]
        ]);


        $result = $mockedImage->generate($model, $text);

        $this->assertEquals($response, $result['data'][0]['url']);
    }
}
