<?php

declare(strict_types=1);

namespace RedditImage\Tests\Transformer\Agnostic;

use Mockery as m;
use RedditImage\Tests\PHPUnit\TestCase;
use RedditImage\Content;
use RedditImage\Settings;
use RedditImage\Transformer\Agnostic\LinkTransformer;

/**
* @covers LinkTransformer
*/
final class LinkTransformerTest extends TestCase
{
    private LinkTransformer $transformer;
    private Content&m\MockInterface $content;
    private Settings&m\MockInterface $settings;

    public function setUp(): void
    {
        parent::setUp();

        $this->content = m::mock(Content::class);
        $this->settings = m::mock(Settings::class);
        $this->transformer = new LinkTransformer($this->settings);
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function testCanTransform(bool $hasRealContent): void
    {
        $this->content->expects('hasReal')
            ->once()
            ->andReturns($hasRealContent);

        $this->assertEquals(!$hasRealContent, $this->transformer->canTransform($this->content));
    }

    public function testTransform(): void
    {
        $this->content->expects('getContentLink')
            ->once()
            ->andReturns('https://example.org');
        $this->settings->expects('getProcessor')
            ->once()
            ->andReturns('test');

        $html = $this->transformer->transform($this->content);

        $this->assertHtmlHasGeneratedContentContainer($html);
        $this->assertHtmlHasLink($html, 'https://example.org');
    }
}
