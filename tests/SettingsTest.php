<?php

declare(strict_types=1);

namespace RedditImage\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use RedditImage\Settings;

/**
 * @covers Settings
 */
final class SettingsTest extends TestCase
{
    public function testWhenNoSettings(): void
    {
        $settings = new Settings([]);

        $this->assertFalse($settings->hasImgurClientId());
        $this->assertEquals('', $settings->getImgurClientId());
        $this->assertEquals(70, $settings->getDefaultImageHeight());
        $this->assertEquals(70, $settings->getImageHeight());
        $this->assertTrue($settings->getMutedVideo());
        $this->assertTrue($settings->getDisplayImage());
        $this->assertTrue($settings->getDisplayVideo());
        $this->assertTrue($settings->getDisplayOriginal());
        $this->assertFalse($settings->getDisplayMetadata());
        $this->assertFalse($settings->getDisplayThumbnails());
        $this->assertEquals(1, $settings->getRedditDelay());
    }

    public function testWhenSettings(): void
    {
        $settings = new Settings([
            'imgurClientId' => 'abc123def',
            'imageHeight' => 90,
            'mutedVideo' => false,
            'displayImage' => false,
            'displayVideo' => false,
            'displayOriginal' => false,
            'displayMetadata' => true,
            'displayThumbnails' => true,
            'redditDelay' => 3,
        ]);

        $this->assertTrue($settings->hasImgurClientId());
        $this->assertEquals('abc123def', $settings->getImgurClientId());
        $this->assertEquals(70, $settings->getDefaultImageHeight());
        $this->assertEquals(90, $settings->getImageHeight());
        $this->assertFalse($settings->getMutedVideo());
        $this->assertFalse($settings->getDisplayImage());
        $this->assertFalse($settings->getDisplayVideo());
        $this->assertFalse($settings->getDisplayOriginal());
        $this->assertTrue($settings->getDisplayMetadata());
        $this->assertTrue($settings->getDisplayThumbnails());
        $this->assertEquals(3, $settings->getRedditDelay());
    }

    public function testProcessor(): void
    {
        $settings = new Settings([]);

        $this->assertEquals('no', $settings->getProcessor());

        $settings->setProcessor('test');

        $this->assertEquals('test', $settings->getProcessor());
    }
}
