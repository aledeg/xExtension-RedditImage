<?php

declare(strict_types=1);

namespace RedditImage\Media;

class Video implements DomElementInterface {
    private $sources = [];
    private $audioTrack;

    public function __construct($type = null, $url = null, $audioTrack = null) {
        if (null !== $type && null !== $url) {
            $this->addSource($type, $url);
        }
        $this->audioTrack = $audioTrack;
    }

    public function addSource($type, $url) {
        $this->sources[$type] = $url;
    }

    public function getSources() {
        return $this->sources;
    }

    public function hasAudioTrack() {
        return null !== $this->audioTrack;
    }

    public function getAudioTrack() {
        return $this->audioTrack;
    }

    public function toDomElement(\DomDocument $domDocument): \DomElement {
        $video = $domDocument->createElement('video');
        $video->setAttribute('controls', 'true');
        $video->setAttribute('preload', 'metadata');
        $video->setAttribute('class', 'reddit-image');

        if ($this->hasAudioTrack()) {
            $audio = $video->appendChild($domDocument->createElement('audio'));
            $audio->setAttribute('controls', true);
            $source = $audio->appendChild($domDocument->createElement('source'));
            $source->setAttribute('src', $this->getAudioTrack());
        }

        foreach ($this->getSources() as $format => $url) {
            $source = $video->appendChild($domDocument->createElement('source'));
            $source->setAttribute('src', $url);
            $source->setAttribute('type', $format);
        }

        return $video;
    }
}
