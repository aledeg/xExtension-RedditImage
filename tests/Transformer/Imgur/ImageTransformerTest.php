<?php

declare(strict_types=1);

namespace RedditImage\Tests\Transformer\Imgur;

use Mockery as m;
use RedditImage\Tests\PHPUnit\TestCase;
use RedditImage\Content;
use RedditImage\Settings;
use RedditImage\Transformer\Imgur\ImageTransformer;

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
        yield 'not an Imgur URL' => ['https://example.org/abc123', false];
        yield 'Imgur URL' => ['https://imgur.com/', false];
        yield 'Imgur URL with album token' => ['https://imgur.com/a/abc123', false];
        yield 'Imgur URL with token' => ['https://imgur.com/abc123', true];
        yield 'Imgur URL with query string' => ['https://imgur.com/abc123?key=value', false];
        yield 'Imgur URL with terminal slash' => ['https://imgur.com/abc123/', false];
        yield 'Imgur URL with extension' => ['https://imgur.com/abc123.png', false];
    }

    public function testTransform(): void
    {
        $this->content->expects('getContentLink')
            ->once()
            ->andReturns('https://example.org/abc123');
        $this->settings->expects('getProcessor')
            ->once()
            ->andReturns('test');

        $html = $this->transformer->transform($this->content);

        $this->assertHtmlHasGeneratedContentContainer($html);
        $this->assertHtmlHasImage($html, 'https://example.org/abc123.png');
    }
}
