<?php

declare(strict_types=1);

namespace RedditImage\Tests\Transformer\Reddit;

use Mockery as m;
use RedditImage\Client\Client;
use RedditImage\Content;
use RedditImage\Settings;
use RedditImage\Tests\PHPUnit\TestCase;
use RedditImage\Transformer\Reddit\GalleryTransformer;

/**
* @covers GalleryTransformer
*/
final class GalleryTransformerTest extends TestCase
{
    private GalleryTransformer $transformer;
    private Content&m\MockInterface $content;
    private Settings&m\MockInterface $settings;

    public function setUp(): void
    {
        parent::setUp();

        $this->content = m::mock(Content::class);
        $this->settings = m::mock(Settings::class);
        $this->transformer = new GalleryTransformer($this->settings);
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
        yield 'not Reddit URL' => ['https://example.org', false];
        yield 'not Reddit gallery URL' => ['https://reddit.com', false];
        yield 'Reddit gallery URL' => ['https://reddit.com/gallery', true];
    }

    public function testTransformWhenNoContent(): void
    {
        $client = m::mock(Client::class);
        $client->expects('jsonGet')
            ->once()
            ->with('https://example.org/.json')
            ->andReturns([]);

        $this->content->expects('getCommentsLink')
            ->once()
            ->andReturns('https://example.org/');

        $this->transformer->setClient($client);
        $this->assertEquals('', $this->transformer->transform($this->content));
    }

    public function testTransformWhenContent(): void
    {
        $client = m::mock(Client::class);
        $client->expects('jsonGet')
            ->once()
            ->with('https://example.org/.json')
            ->andReturns([[
                'data' => [
                    'children' => [[
                        'data' => [
                            'media_metadata' => [
                                'image1of2' => [
                                    'm' => 'image/jpg',
                                ],
                                'image2of2' => [
                                    'm' => 'image/jpg',
                                ],
                            ],
                        ],
                    ]],
                ],
            ]]);

        $this->content->expects('getCommentsLink')
            ->once()
            ->andReturns('https://example.org/');
        $this->settings->expects('getProcessor')
            ->once()
            ->andReturns('test');
        $this->settings->expects('getRedditDelay')
            ->once()
            ->andReturns(0);

        $this->transformer->setClient($client);
        $html = $this->transformer->transform($this->content);

        $this->assertHtmlHasGeneratedContentContainer($html);
        $this->assertHtmlHasImage($html, 'https://i.redd.it/image1of2.jpg');
        $this->assertHtmlHasImage($html, 'https://i.redd.it/image2of2.jpg');
    }

    public function testTransformWhenCrosspostedContent(): void
    {
        $client = m::mock(Client::class);
        $client->expects('jsonGet')
            ->once()
            ->with('https://example.org/.json')
            ->andReturns([[
                'data' => [
                    'children' => [[
                        'data' => [
                            'crosspost_parent_list' => [[
                                'media_metadata' => [
                                    'image1of2' => [
                                        'm' => 'image/jpg',
                                    ],
                                    'image2of2' => [
                                        'm' => 'image/jpg',
                                    ],
                                ],
                            ]],
                        ],
                    ]],
                ],
            ]]);

        $this->content->expects('getCommentsLink')
            ->once()
            ->andReturns('https://example.org/');
        $this->settings->expects('getProcessor')
            ->once()
            ->andReturns('test');
        $this->settings->expects('getRedditDelay')
            ->once()
            ->andReturns(0);

        $this->transformer->setClient($client);
        $html = $this->transformer->transform($this->content);

        $this->assertHtmlHasGeneratedContentContainer($html);
        $this->assertHtmlHasImage($html, 'https://i.redd.it/image1of2.jpg');
        $this->assertHtmlHasImage($html, 'https://i.redd.it/image2of2.jpg');
    }
}
