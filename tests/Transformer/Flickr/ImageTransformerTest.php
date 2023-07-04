<?php

namespace RedditImage\Tests\Transformer\Flickr;

use Mockery as m;
use RedditImage\Client\Client;
use RedditImage\Content;
use RedditImage\Settings;
use RedditImage\Tests\PHPUnit\TestCase;
use RedditImage\Transformer\Flickr\ImageTransformer;

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
    public function testCanTransform(string $link, bool $hasApiKey, bool $expected): void
    {
        $this->content->expects('getContentLink')
            ->once()
            ->andReturns($link);
        $this->settings->expects('hasFlickrApiKey')
            ->once()
            ->andReturns($hasApiKey);

        $this->assertEquals($expected, $this->transformer->canTransform($this->content));
    }

    public static function provideDataForCanTransform(): \Generator
    {
        yield 'api key, not a Flickr URL' => ['https://example.org', true, false];
        yield 'api key, Flickr URL without user id nor image id' => ['https://flickr.com', true, false];
        yield 'api key, Flickr image URL' => ['https://flickr.com/photos/abc123@456/abc123', true, true];
        yield 'no api key, not a Flickr URL' => ['https://example.org', false, false];
        yield 'no api key, Flickr URL without user id nor image id' => ['https://flickr.com', false, false];
        yield 'no api key, Flickr image URL' => ['https://flickr.com/photos/abc123@456/abc123', false, false];
    }

    public function testTransformWhenNoContent(): void
    {
        $client = m::mock(Client::class);
        $client->expects('jsonGet')
            ->once()
            ->with(
                'https://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key=xyz123&photo_id=hello&format=json',
                [],
                m::on(static function ($argument) {
                    return $argument !== null;
                })
            )
            ->andReturns([]);

        $this->content->expects('getContentLink')
            ->once()
            ->andReturns('https://flickr.com/photos/abc123@456/hello');
        $this->settings->expects('getFlickrApiKey')
            ->once()
            ->andReturns('xyz123');

        $this->transformer->setClient($client);
        $this->assertEquals('', $this->transformer->transform($this->content));
    }

    /**
     * @dataProvider provideDataWithoutImageLinkForTransform
     * @param mixed[] $response
     */
    public function testTransformWhenNoImageLink(array $response): void
    {
        $client = m::mock(Client::class);
        $client->expects('jsonGet')
            ->once()
            ->with(
                'https://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key=xyz123&photo_id=hello&format=json',
                [],
                m::on(static function ($argument) {
                    return $argument !== null;
                })
            )
            ->andReturns($response);

        $this->content->expects('getContentLink')
            ->once()
            ->andReturns('https://flickr.com/photos/abc123@456/hello');
        $this->settings->expects('getFlickrApiKey')
            ->once()
            ->andReturns('xyz123');

        $this->transformer->setClient($client);
        $this->assertEquals('', $this->transformer->transform($this->content));
    }

    public static function provideDataWithoutImageLinkForTransform(): \Generator
    {
        yield 'empty data' => [[]];
        yield 'missing source' => [['sizes' => ['size' => [['source' => '']]]]];
    }

    public function testTransformWhenImageLink(): void
    {
        $client = m::mock(Client::class);
        $client->expects('jsonGet')
            ->once()
            ->with(
                'https://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key=xyz123&photo_id=hello&format=json',
                [],
                m::on(static function ($argument) {
                    return $argument !== null;
                })
            )
            ->andReturns([
                'sizes' => [
                    'size' => [
                        [
                            'source' => 'https://example.org/hello.64.png',
                        ],
                        [
                            'source' => 'https://example.org/hello.128.png',
                        ],
                        [
                            'source' => 'https://example.org/hello.256.png',
                        ],
                    ],
                ],
            ]);

        $this->content->expects('getContentLink')
            ->once()
            ->andReturns('https://flickr.com/photos/abc123@456/hello');
        $this->settings->expects('getFlickrApiKey')
            ->once()
            ->andReturns('xyz123');
        $this->settings->expects('getProcessor')
            ->once()
            ->andReturns('test');

        $this->transformer->setClient($client);
        $html = $this->transformer->transform($this->content);

        $this->assertHtmlHasGeneratedContentContainer($html);
        $this->assertHtmlHasImage($html, 'https://example.org/hello.256.png');
    }
}
