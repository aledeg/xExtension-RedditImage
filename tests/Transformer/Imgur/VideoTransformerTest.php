<?php

declare(strict_types=1);

namespace RedditImage\Tests\Transformer\Imgur;

use Mockery as m;
use RedditImage\Tests\PHPUnit\TestCase;
use RedditImage\Content;
use RedditImage\Settings;
use RedditImage\Transformer\Imgur\VideoTransformer;

/**
 * @covers VideoTransformer
 */
final class VideoTransformerTest extends TestCase
{
    private VideoTransformer $transformer;
    private Content&m\MockInterface $content;
    private Settings&m\MockInterface $settings;

    public function setUp(): void
    {
        parent::setUp();

        $this->content = m::mock(Content::class);
        $this->settings = m::mock(Settings::class);
        $this->transformer = new VideoTransformer($this->settings);
    }

    /**
     * @dataProvider provideDataForCanTransform
     */
    public function testCanTransform(string $url, bool $expected): void
    {
        $this->content->expects('getContentLink')
            ->once()
            ->andReturns($url);

        $this->assertEquals($expected, $this->transformer->canTransform($this->content));
    }

    public static function provideDataForCanTransform(): \Generator
    {
        yield 'not Imgur URL' => ['https://example.org', false];
        yield 'Imgur URL without video' => ['https://imgur.com/image.jpg', false];
        yield 'Imgur URL with video' => ['https://imgur.com/video.gifv', true];
        yield 'Imgur URL with video and query string' => ['https://imgur.com/video.gifv?key=value', false];
        yield 'Imgur URL with video and route parameter' => ['https://imgur.com/videos/video.gifv', false];
    }

    public function testTransform(): void
    {
        $this->content->expects('getContentLink')
            ->once()
            ->andReturns('https://imgur.com/video.gifv');
        $this->settings->expects('getProcessor')
            ->once()
            ->andReturns('test');

        $html = $this->transformer->transform($this->content);

        $this->assertHtmlHasGeneratedContentContainer($html);
        $this->assertHtmlHasMp4Video($html, 'https://imgur.com/video.mp4');
    }
}
