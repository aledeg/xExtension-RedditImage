<?php

declare(strict_types=1);

namespace RedditImage\Tests\Transformer\Agnostic;

use Mockery as m;
use RedditImage\Client\Client;
use RedditImage\Content;
use RedditImage\Settings;
use RedditImage\Tests\PHPUnit\TestCase;
use RedditImage\Transformer\Agnostic\VideoTransformer;

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
        yield 'MP4 video without query string' => ['https://example.org/video.mp4', true];
        yield 'WEBM video without query string' => ['https://example.org/video.webm', true];
        yield 'MP4 video with query string' => ['https://example.org/video.mp4?key=value', false];
        yield 'WEBM video with query string' => ['https://example.org/video.webm?key=value', false];
        yield 'MP4 format in query string' => ['https://example.org/video?format=mp4', false];
        yield 'WEBM format in query string' => ['https://example.org/video?format=webm', false];
        yield 'MP4 as route section' => ['https://example.org/mp4', false];
        yield 'WEBM as route section' => ['https://example.org/webm', false];
    }

    /**
     * @testWith ["https://example.org/video.mp4"]
     *           ["https://example.org/video.webm"]
     */
    public function testTransformWithInaccessibleResource(string $url): void
    {
        $client = m::mock(Client::class);
        $client->expects('isAccessible')
            ->once()
            ->with($url)
            ->andReturnFalse();

        $this->content->expects('getContentLink')
            ->once()
            ->andReturns($url);

        $this->transformer->setClient($client);
        $this->assertEquals('', $this->transformer->transform($this->content));
    }

    public function testTransformWithAccessibleMp4Resource(): void
    {
        $url = 'https://example.org/video.mp4';

        $client = m::mock(Client::class);
        $client->expects('isAccessible')
            ->once()
            ->with($url)
            ->andReturnTrue();

        $this->content->expects('getContentLink')
            ->once()
            ->andReturns($url);
        $this->settings->expects('getProcessor')
            ->once()
            ->andReturns('test');

        $this->transformer->setClient($client);
        $html = $this->transformer->transform($this->content);

        $this->assertHtmlHasGeneratedContentContainer($html);
        $this->assertHtmlHasMp4Video($html, $url);
    }

    public function testTransformWithAccessibleWebmResource(): void
    {
        $url = 'https://example.org/video.webm';

        $client = m::mock(Client::class);
        $client->expects('isAccessible')
            ->once()
            ->with($url)
            ->andReturnTrue();

        $this->content->expects('getContentLink')
            ->once()
            ->andReturns($url);
        $this->settings->expects('getProcessor')
            ->once()
            ->andReturns('test');

        $this->transformer->setClient($client);
        $html = $this->transformer->transform($this->content);

        $this->assertHtmlHasGeneratedContentContainer($html);
        $this->assertHtmlHasWebmVideo($html, $url);
    }
}
