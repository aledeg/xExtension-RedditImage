<?php

declare(strict_types=1);

namespace RedditImage\Media;

class Video implements DomElementInterface
{
    /** @var array<string, string> */
    private array $sources = [];
    private ?string $audioTrack;

    public function __construct(?string $type = null, ?string $url = null, ?string $audioTrack = null)
    {
        if (null !== $type && null !== $url) {
            $this->addSource($type, $url);
        }
        $this->audioTrack = $audioTrack;
    }

    public function addSource(string $type, string $url): void
    {
        $this->sources[$type] = $url;
    }

    private function hasAudioTrack(): bool
    {
        return null !== $this->audioTrack;
    }

    public function toDomElement(\DomDocument $domDocument): \DomElement
    {
        $video = $domDocument->createElement('video');
        $video->setAttribute('controls', 'true');
        $video->setAttribute('preload', 'metadata');
        $video->setAttribute('class', 'reddit-image');

        if ($this->hasAudioTrack()) {
            $audio = $domDocument->createElement('audio');
            $audio->setAttribute('controls', 'true');
            $video->appendChild($audio);
            $source = $domDocument->createElement('source');
            $source->setAttribute('src', $this->audioTrack);
            $audio->appendChild($source);
        }

        foreach ($this->sources as $format => $url) {
            $source = $domDocument->createElement('source');
            $source->setAttribute('src', $url);
            $source->setAttribute('type', $format);
            $video->appendChild($source);
        }

        return $video;
    }
}
