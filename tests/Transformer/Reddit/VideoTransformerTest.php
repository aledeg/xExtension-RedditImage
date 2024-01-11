<?php

declare(strict_types=1);

namespace RedditImage\Tests\Transformer\Reddit;

use Mockery as m;
use RedditImage\Client\Client;
use RedditImage\Content;
use RedditImage\Settings;
use RedditImage\Tests\PHPUnit\TestCase;
use RedditImage\Transformer\Reddit\VideoTransformer;

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
        yield 'not Reddit URL' => ['https://example.org', false];
        yield 'not Redd.it URL' => ['https://reddit.com', false];
        yield 'not Redd.it video URL' => ['https://redd.it', false];
        yield 'Redd.it video URL' => ['https://v.redd.it', true];
        yield 'Redd.it video URL with query string' => ['https://v.redd.it?key=param', true];
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
                            'media' => [
                                'reddit_video' => [
                                    'fallback_url' => 'https://example.org/DASH_video.mp4?source=fallback',
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
        $this->assertHtmlHasMp4VideoWithAudio($html, 'https://example.org/DASH_video.mp4', 'https://example.org/DASH_audio.mp4');
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
                                'media' => [
                                    'reddit_video' => [
                                        'fallback_url' => 'https://example.org/DASH_video.mp4?source=fallback',
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
        $this->assertHtmlHasMp4VideoWithAudio($html, 'https://example.org/DASH_video.mp4', 'https://example.org/DASH_audio.mp4');
    }
}
