<?php

declare(strict_types=1);

namespace RedditImage;

class Settings
{
    private const DEFAULT_IMAGEHEIGHT = 70;
    private const DEFAULT_MUTEDVIDEO = true;
    private const DEFAULT_DISPLAYIMAGE = true;
    private const DEFAULT_DISPLAYVIDEO = true;
    private const DEFAULT_DISPLAYORIGINAL = true;
    private const DEFAULT_DISPLAYMETADATA = false;
    private const DEFAULT_DISPLAYTHUMBNAILS = false;
    private const DEFAULT_REDDITDELAY = 1;

    private string $processor = 'no';
    /** @var array<string, string|int|bool> */
    private array $settings;

    /**
     * @param array<string, string|int|bool> $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function hasImgurClientId(): bool
    {
        return $this->getImgurClientId() !== '';
    }

    public function getImgurClientId(): string
    {
        if (array_key_exists('imgurClientId', $this->settings)) {
            return (string) $this->settings['imgurClientId'];
        }

        return '';
    }

    public function hasFlickrApiKey(): bool
    {
        return $this->getFlickrApiKey() !== '';
    }

    public function getFlickrApiKey(): string
    {
        if (array_key_exists('flickrApiKey', $this->settings)) {
            return (string) $this->settings['flickrApiKey'];
        }

        return '';
    }

    public function getDefaultImageHeight(): int
    {
        return self::DEFAULT_IMAGEHEIGHT;
    }

    public function getImageHeight(): int
    {
        if (array_key_exists('imageHeight', $this->settings)) {
            return (int) $this->settings['imageHeight'];
        }

        return self::DEFAULT_IMAGEHEIGHT;
    }

    public function getMutedVideo(): bool
    {
        if (array_key_exists('mutedVideo', $this->settings)) {
            return (bool) $this->settings['mutedVideo'];
        }

        return self::DEFAULT_MUTEDVIDEO;
    }

    public function getDisplayImage(): bool
    {
        if (array_key_exists('displayImage', $this->settings)) {
            return (bool) $this->settings['displayImage'];
        }

        return self::DEFAULT_DISPLAYIMAGE;
    }

    public function getDisplayVideo(): bool
    {
        if (array_key_exists('displayVideo', $this->settings)) {
            return (bool) $this->settings['displayVideo'];
        }

        return self::DEFAULT_DISPLAYVIDEO;
    }

    public function getDisplayOriginal(): bool
    {
        if (array_key_exists('displayOriginal', $this->settings)) {
            return (bool) $this->settings['displayOriginal'];
        }

        return self::DEFAULT_DISPLAYORIGINAL;
    }

    public function getDisplayMetadata(): bool
    {
        if (array_key_exists('displayMetadata', $this->settings)) {
            return (bool) $this->settings['displayMetadata'];
        }

        return self::DEFAULT_DISPLAYMETADATA;
    }

    public function getDisplayThumbnails(): bool
    {
        if (array_key_exists('displayThumbnails', $this->settings)) {
            return (bool) $this->settings['displayThumbnails'];
        }

        return self::DEFAULT_DISPLAYTHUMBNAILS;
    }

    public function getRedditDelay(): int
    {
        if (array_key_exists('redditDelay', $this->settings)) {
            return (int) $this->settings['redditDelay'];
        }

        return self::DEFAULT_REDDITDELAY;
    }

    public function getProcessor(): string
    {
        return $this->processor;
    }

    public function setProcessor(string $processor): void
    {
        $this->processor = $processor;
    }
}
