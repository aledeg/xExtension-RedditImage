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
final class ImageTransformerTest extends TestCase
{
    private ImageTransformer $transformer;
    private Content&m\MockInterface $content;
    private Settings&m\MockInterface $settings;

    public function setUp(): void
    {
        parent::setUp();

        $this->content = m::mock(Content::class);
        $this->settings = m::mock(Settings::class);
        $this->transformer = new ImageTransformer($this->settings);
    }

    /**
     * @dataProvider provideDataForCanTransform
     */
    public function testCanTransform(string $link, bool $expected): void
    {
        $this->content->expects('getContentLink')
            ->once()
            ->andReturns($link);

        $this->assertEquals($expected, $this->transformer->canTransform($this->content));
    }

    public static function provideDataForCanTransform(): \Generator
    {
        foreach(['jpg', 'jpeg', 'png', 'bmp', 'gif'] as $format) {
            yield from self::provideDataForFormat($format);
        }
        yield 'redgifs images are not suppported' => ['https://i.redgifs.com/i/image.jpg', false];
    }

    private static function provideDataForFormat(string $format): \Generator
    {
        yield "{$format} image without query string" => ["https://example.org/image.{$format}", true];
        yield "{$format} image with query string" => ["https://example.org/image.{$format}?key=value", true];
        yield "{$format} format in query string" => ["https://example.org/image?format={$format}", false];
        yield "{$format} as route section" => ["https://example.org/{$format}", false];
    }

    /**
     * @dataProvider provideDataForTransform
     */
    public function testTransform(string $input, string $expected): void
    {
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

    public static function provideDataForTransform(): \Generator
    {
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
