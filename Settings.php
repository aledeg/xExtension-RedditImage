<?php

declare(strict_types=1);

namespace RedditImage;

class Settings {
    private const DEFAULT_IMAGEHEIGHT = 70;
    private const DEFAULT_MUTEDVIDEO = true;
    private const DEFAULT_DISPLAYIMAGE = true;
    private const DEFAULT_DISPLAYVIDEO = true;
    private const DEFAULT_DISPLAYORIGINAL = true;
    private const DEFAULT_DISPLAYMETADATA = false;
    private const DEFAULT_DISPLAYTHUMBNAILS = false;

    private string $processor = 'no';
    private array $settings;

    public function __construct(array $settings) {
        $this->settings = $settings;
    }

    public function hasImgurClientId(): bool {
        return $this->getImgurClientId() !== '';
    }

    public function getImgurClientId(): string {
        return $this->settings['imgurClientId'] ?? '';
    }

    public function hasFlickrApiKey(): bool {
        return $this->getFlickrApiKey() !== '';
    }

    public function getFlickrApiKey(): string {
        return $this->settings['flickrApiKey'] ?? '';
    }

    public function getDefaultImageHeight(): int {
        return static::DEFAULT_IMAGEHEIGHT;
    }

    public function getImageHeight(): int {
        return $this->settings['imageHeight'] ?? static::DEFAULT_IMAGEHEIGHT;
    }

    public function getMutedVideo(): bool {
        return $this->settings['mutedVideo'] ?? static::DEFAULT_MUTEDVIDEO;
    }

    public function getDisplayImage(): bool {
        return $this->settings['displayImage'] ?? static::DEFAULT_DISPLAYIMAGE;
    }

    public function getDisplayVideo(): bool {
        return $this->settings['displayVideo'] ?? static::DEFAULT_DISPLAYVIDEO;
    }

    public function getDisplayOriginal(): bool {
        return $this->settings['displayOriginal'] ?? static::DEFAULT_DISPLAYORIGINAL;
    }

    public function getDisplayMetadata(): bool {
        return $this->settings['displayMetadata'] ?? static::DEFAULT_DISPLAYMETADATA;
    }

    public function getDisplayThumbnails(): bool {
        return $this->settings['displayThumbnails'] ?? static::DEFAULT_DISPLAYTHUMBNAILS;
    }

    public function getProcessor(): string {
        return $this->processor;
    }

    public function setProcessor(string $processor): void {
        $this->processor = $processor;
    }
}
