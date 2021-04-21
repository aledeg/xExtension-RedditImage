<?php

namespace RedditImage\Media;

class Video {
    private $sources = [];

    public function __construct($type = null, $url = null) {
        if (null !== $type && null !== $url) {
            $this->addSource($type, $url);
        }
    }

    public function addSource($type, $url) {
        $this->sources[$type] = $url;
    }

    public function getSources() {
        return $this->sources;
    }
}
