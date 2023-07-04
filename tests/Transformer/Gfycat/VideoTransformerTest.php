<?php

declare(strict_types=1);

namespace RedditImage\Tests\Transformer\Gfycat;

use Mockery as m;
use RedditImage\Client\Client;
use RedditImage\Content;
use RedditImage\Settings;
use RedditImage\Tests\PHPUnit\TestCase;
use RedditImage\Transformer\Gfycat\VideoTransformer;

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
        yield 'not Gfycat URL' => ['https://example.org', false];
        yield 'Gfycat without token' => ['https://gfycat.com/', false];
        yield 'Gfycat with token' => ['https://gfycat.com/abc123', true];
        yield 'Gfycat with "token" containing -' => ['https://gfycat.com/abc-123', false];
        yield 'Gfycat with "token" containing .' => ['https://gfycat.com/abc.123', false];
        yield 'Gfycat with token and route parameters' => ['https://gfycat.com/abc/def/ghi123', true];
    }

    public function testTransformWhenNoContent(): void
    {
        $client = m::mock(Client::class);
        $client->expects('jsonGet')
            ->once()
            ->with('https://api.gfycat.com/v1/gfycats/hello')
            ->andReturns([]);

        $this->content->expects('getContentLink')
            ->once()
            ->andReturns('https://gfycat.com/hello');

        $this->transformer->setClient($client);
        $this->assertEquals('', $this->transformer->transform($this->content));
    }

    public function testTransformWhenContent(): void
    {
        $client = m::mock(Client::class);
        $client->expects('jsonGet')
            ->once()
            ->with('https://api.gfycat.com/v1/gfycats/hello')
            ->andReturns([
                'gfyItem' => [
                    'mp4Url' => 'https://gfycat.com/hello.mp4',
                    'webmUrl' => 'https://gfycat.com/hello.webm',
                ],
            ]);

        $this->content->expects('getContentLink')
            ->once()
            ->andReturns('https://gfycat.com/hello');
        $this->settings->expects('getProcessor')
            ->once()
            ->andReturns('test');

        $this->transformer->setClient($client);
        $html = $this->transformer->transform($this->content);

        $this->assertHtmlHasGeneratedContentContainer($html);
        $this->assertHtmlHasMp4Video($html, 'https://gfycat.com/hello.mp4');
        $this->assertHtmlHasWebmVideo($html, 'https://gfycat.com/hello.webm');
    }
}
