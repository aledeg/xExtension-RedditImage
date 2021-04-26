<?php

namespace RedditImage\Media;

class Video {
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
}
