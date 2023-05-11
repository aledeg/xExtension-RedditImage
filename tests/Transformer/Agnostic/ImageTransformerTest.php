<?php

declare(strict_types=1);

namespace RedditImage\Tests\Transformer\Agnostic;

use Mockery as m;
use RedditImage\Tests\PHPUnit\TestCase;
use RedditImage\Content;
use RedditImage\Settings;
use RedditImage\Transformer\Agnostic\ImageTransformer;

 /**
 * @covers ImageTransformer
 */
final class ImageTransformerTest extends TestCase {
    private ImageTransformer $transformer;
    private Content&m\MockInterface $content;
    private Settings&m\MockInterface $settings;

    public function setUp(): void {
        parent::setUp();

        $this->content = m::mock(Content::class);
        $this->settings = m::mock(Settings::class);
        $this->transformer = new ImageTransformer($this->settings);
    }

    /**
     * @dataProvider provideDataForCanTransform
     */
    public function testCanTransform(string $link, bool $expected): void {
        $this->content->expects('getContentLink')
            ->once()
            ->andReturns($link);
        
        $this->assertEquals($expected, $this->transformer->canTransform($this->content));
    }

    public static function provideDataForCanTransform(): \Generator {
        yield 'JPG image without query string' => ['https://example.org/image.jpg', true];
        yield 'JPEG image without query string' => ['https://example.org/image.jpeg', true];
        yield 'PNG image without query string' => ['https://example.org/image.png', true];
        yield 'BMP image without query string' => ['https://example.org/image.bmp', true];
        yield 'GIF image without query string' => ['https://example.org/image.gif', true];
        yield 'JPG image with query string' => ['https://example.org/image.jpg?key=value', true];
        yield 'JPEG image with query string' => ['https://example.org/image.jpeg?key=value', true];
        yield 'PNG image with query string' => ['https://example.org/image.png?key=value', true];
        yield 'BMP image with query string' => ['https://example.org/image.bmp?key=value', true];
        yield 'GIF image with query string' => ['https://example.org/image.gif?key=value', true];
        yield 'JPG format in query string' => ['https://example.org/image?format=jpg', false];
        yield 'JPEG format in query string' => ['https://example.org/image?format=jpeg', false];
        yield 'PNG format in query string' => ['https://example.org/image?format=png', false];
        yield 'BMP format in query string' => ['https://example.org/image?format=bmp', false];
        yield 'GIF format in query string' => ['https://example.org/image?format=gif', false];
        yield 'JPG as route section' => ['https://example.org/jpg', false];
        yield 'JPEG as route section' => ['https://example.org/jpeg', false];
        yield 'PNG as route section' => ['https://example.org/png', false];
        yield 'BMP as route section' => ['https://example.org/bmp', false];
        yield 'GIF as route section' => ['https://example.org/gif', false];
    }

    /**
     * @dataProvider provideDataForTransform
     */
    public function testTransform(string $input, string $expected): void {
        $this->content->expects('getContentLink')
            ->once()
            ->andReturns($input);
        $this->settings->expects('getProcessor')
            ->once()
            ->andReturns('test');

        $html = $this->transformer->transform($this->content);

        $this->assertHtmlHasGeneratedContentContainer($html);
        $this->assertHtmlHasImage($html, $expected);
    }

    public static function provideDataForTransform(): \Generator {
        yield 'JPG image without query string' => ['https://example.org/image.jpg', 'https://example.org/image.jpg'];
        yield 'JPEG image without query string' => ['https://example.org/image.jpeg', 'https://example.org/image.jpeg'];
        yield 'PNG image without query string' => ['https://example.org/image.png', 'https://example.org/image.png'];
        yield 'BMP image without query string' => ['https://example.org/image.bmp', 'https://example.org/image.bmp'];
        yield 'GIF image without query string' => ['https://example.org/image.gif', 'https://example.org/image.gif'];
        yield 'JPG image with query string' => ['https://example.org/image.jpg?key=value', 'https://example.org/image.jpg'];
        yield 'JPEG image with query string' => ['https://example.org/image.jpeg?key=value', 'https://example.org/image.jpeg'];
        yield 'PNG image with query string' => ['https://example.org/image.png?key=value', 'https://example.org/image.png'];
        yield 'BMP image with query string' => ['https://example.org/image.bmp?key=value', 'https://example.org/image.bmp'];
        yield 'GIF image with query string' => ['https://example.org/image.gif?key=value', 'https://example.org/image.gif'];
    }
}
