<?php

declare(strict_types=1);

namespace RedditImage\Tests\Transformer\Imgur;

use Mockery as m;
use RedditImage\Client\Client;
use RedditImage\Content;
use RedditImage\Settings;
use RedditImage\Tests\PHPUnit\TestCase;
use RedditImage\Transformer\Imgur\GalleryWithClientIdTransformer;

/**
* @covers GalleryWithClientIdTransformer
*/
final class GalleryWithClientIdTransformerTest extends TestCase
{
    private GalleryWithClientIdTransformer $transformer;
    private Content&m\MockInterface $content;
    private Settings&m\MockInterface $settings;

    public function setUp(): void
    {
        parent::setUp();

        $this->content = m::mock(Content::class);
        $this->settings = m::mock(Settings::class);
        $this->transformer = new GalleryWithClientIdTransformer($this->settings);
    }

    /**
     * @dataProvider provideDataForCanTransform
     */
    public function testCanTransform(string $link, bool $hasClientId, bool $expected): void
    {
        $this->content->expects('getContentLink')
            ->once()
            ->andReturns($link);
        $this->settings->expects('hasImgurClientId')
            ->once()
            ->andReturns($hasClientId);

        $this->assertEquals($expected, $this->transformer->canTransform($this->content));
    }

    public static function provideDataForCanTransform(): \Generator
    {
        yield 'client id, not an Imgur URL' => ['https://example.org', true, false];
        yield 'client id, not an Imgur album URL' => ['https://imgur.com/image.png', false, false];
        yield 'client id, Imgur album URL' => ['https://imgur.com/a/abc123', true, true];
        yield 'client id, Imgur album URL with query string' => ['https://imgur.com/a/abc123?key=value', true, true];
        yield 'no client id, not an Imgur URL' => ['https://example.org', false, false];
        yield 'no client id, not an Imgur album URL' => ['https://imgur.com/image.png', false, false];
        yield 'no client id, Imgur album URL' => ['https://imgur.com/a/abc123', false, false];
        yield 'no client id, Imgur album URL with query string' => ['https://imgur.com/a/abc123?key=value', false, false];
    }

    public function testTransformWhenNoContent(): void
    {
        $client = m::mock(Client::class);
        $client->expects('jsonGet')
            ->once()
            ->with('https://api.imgur.com/3/album/hello/images', [
                'Authorization: Client-ID abc123',
            ])
            ->andReturns([]);

        $this->content->expects('getContentLink')
            ->once()
            ->andReturns('https://imgur.com/a/hello');
        $this->settings->expects('getImgurClientId')
            ->once()
            ->andReturns('abc123');

        $this->transformer->setClient($client);
        $this->assertEquals('', $this->transformer->transform($this->content));
    }

    public function testTransformWhenContent(): void
    {
        $client = m::mock(Client::class);
        $client->expects('jsonGet')
            ->once()
            ->with('https://api.imgur.com/3/album/hello/images', [
                'Authorization: Client-ID abc123',
            ])
            ->andReturns([
                'data' => [
                    [
                        'type' => 'video/mp4',
                        'link' => 'https://example.org/hello.mp4',
                    ],
                    [
                        'type' => 'image/jpg',
                        'link' => 'https://example.org/hello.jpg',
                    ],
                    [
                        'type' => 'video/webm',
                        'link' => 'https://example.org/hello.webm',
                    ],
                    [
                        'type' => 'image/png',
                        'link' => 'https://example.org/hello.png',
                    ],
                ],
            ]);

        $this->content->expects('getContentLink')
            ->once()
            ->andReturns('https://imgur.com/a/hello');
        $this->settings->expects('getImgurClientId')
            ->once()
            ->andReturns('abc123');
        $this->settings->expects('getProcessor')
            ->once()
            ->andReturns('test');

        $this->transformer->setClient($client);
        $html = $this->transformer->transform($this->content);

        $this->assertHtmlHasGeneratedContentContainer($html);
        $this->assertHtmlHasImage($html, 'https://example.org/hello.png');
        $this->assertHtmlHasImage($html, 'https://example.org/hello.jpg');
        $this->assertHtmlHasMp4Video($html, 'https://example.org/hello.mp4');
        $this->assertHtmlHasWebmVideo($html, 'https://example.org/hello.webm');
    }
}
