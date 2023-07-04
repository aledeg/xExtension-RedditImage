<?php

declare(strict_types=1);

namespace RedditImage\Tests\Media;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use RedditImage\Media\DomElementInterface;
use RedditImage\Media\Link;

/**
 * @covers Link
 */
final class LinkTest extends TestCase
{
    public function test(): void
    {
        $media = new Link('https://example.org');

        $this->assertInstanceOf(DomElementInterface::class, $media);

        $textNode = m::mock(\DomText::class);

        $aElement = m::mock(\DomElement::class);
        $aElement->expects('setAttribute')
            ->once()
            ->with('href', 'https://example.org');
        $aElement->expects('appendChild')
            ->once()
            ->with($textNode);

        $pElement = m::mock(\DomElement::class);
        $pElement->expects('appendChild')
            ->once()
            ->with($aElement)
            ->andReturns($aElement);

        $domDocument = m::mock(\DomDocument::class);
        $domDocument->expects('createElement')
            ->once()
            ->with('p')
            ->andReturns($pElement);
        $domDocument->expects('createElement')
            ->once()
            ->with('a')
            ->andReturns($aElement);
        $domDocument->expects('createTextNode')
            ->once()
            ->with('https://example.org')
            ->andReturns($textNode);

        $this->assertEquals($pElement, $media->toDomElement($domDocument));
    }
}
