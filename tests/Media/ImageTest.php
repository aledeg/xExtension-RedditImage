<?php

declare(strict_types=1);

namespace RedditImage\Tests\Media;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use RedditImage\Media\DomElementInterface;
use RedditImage\Media\Image;

/**
 * @covers Image
 */
final class ImageTest extends TestCase
{
    public function test(): void
    {
        $media = new Image('https://example.org');

        $this->assertInstanceOf(DomElementInterface::class, $media);

        $domElement = m::mock(\DomElement::class);
        $domElement->expects('setAttribute')
            ->once()
            ->with('src', 'https://example.org');
        $domElement->expects('setAttribute')
            ->once()
            ->with('class', 'reddit-image');

        $domDocument = m::mock(\DomDocument::class);
        $domDocument->expects('createElement')
            ->once()
            ->with('img')
            ->andReturns($domElement);

        $this->assertEquals($domElement, $media->toDomElement($domDocument));
    }
}
