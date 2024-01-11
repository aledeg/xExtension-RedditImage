<?php

declare(strict_types=1);

namespace RedditImage\Tests\PHPUnit;

use PHPUnit\Framework\TestCase as BaseTestCase;
use RedditImage\Tests\PHPUnit\Constraint\htmlHasGeneratedContentContainer;
use RedditImage\Tests\PHPUnit\Constraint\htmlHasImage;
use RedditImage\Tests\PHPUnit\Constraint\htmlHasLink;
use RedditImage\Tests\PHPUnit\Constraint\htmlHasVideo;
use RedditImage\Tests\PHPUnit\Constraint\htmlHasVideoWithAudio;

class TestCase extends BaseTestCase
{
    public static function assertHtmlHasGeneratedContentContainer(string $html, string $message = ''): void
    {
        self::assertThat($html, new htmlHasGeneratedContentContainer(), $message);
    }

    public static function assertHtmlHasImage(string $html, string $url, string $message = ''): void
    {
        self::assertThat($html, new htmlHasImage($url), $message);
    }

    public static function assertHtmlHasLink(string $html, string $url, string $message = ''): void
    {
        self::assertThat($html, new htmlHasLink($url), $message);
    }

    public static function assertHtmlHasMp4Video(string $html, string $url, string $message = ''): void
    {
        self::assertThat($html, new htmlHasVideo('mp4', $url), $message);
    }

    public static function assertHtmlHasWebmVideo(string $html, string $url, string $message = ''): void
    {
        self::assertThat($html, new htmlHasVideo('webm', $url), $message);
    }

    public static function assertHtmlHasMp4VideoWithAudio(string $html, string $videoUrl, string $audioUrl, string $message = ''): void
    {
        self::assertThat($html, new htmlHasVideoWithAudio('mp4', $videoUrl, $audioUrl), $message);
    }
}
