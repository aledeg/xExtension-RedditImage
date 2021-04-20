<?php

namespace RedditImage\Transformer;

use RedditImage\Media\Image;

abstract class AbstractTransformer {
    const MATCH_REDDIT = 'reddit.com';

    /**
     * @return bool
     */
    protected function isRedditLink($entry) {
        return (bool) strpos($entry->link(), static::MATCH_REDDIT);
    }

    /**
     * @return object
     */
    abstract public function transform($entry);

    /**
     * @return string
     */
    protected function getOriginComment($origin) {
        $className = (new \ReflectionClass($this))->getShortName();

        return "xExtension-RedditImage | $className | $origin";
    }

    /**
     * @param string $origin
     * @param array $media
     * @return \DomDocument
     */
    protected function generateImageDom($origin, $media = []) {
        $dom = new \DomDocument();

        $div = $dom->appendChild($dom->createElement('div'));
        $div->setAttribute('class', 'reddit-image figure');

        $div->appendChild($dom->createComment($this->getOriginComment($origin)));

        foreach ($media as $medium) {
            if (!$medium instanceof Image) {
                continue;
            }
            $img = $div->appendChild($dom->createElement('img'));
            $img->setAttribute('src', $medium->getUrl());
            $img->setAttribute('class', 'reddit-image');
        }

        return $dom;
    }

    /**
     * @param string $origin
     * @param array $details
     * @return \DomDocument
     */
    protected function generateVideoDom($origin, $details = []) {
        $dom = new \DomDocument();

        $div = $dom->appendChild($dom->createElement('div'));
        $div->setAttribute('class', 'reddit-image figure');

        $div->appendChild($dom->createComment($this->getOriginComment($origin)));

        $video = $div->appendChild($dom->createElement('video'));
        $video->setAttribute('controls', true);
        $video->setAttribute('preload', 'metadata');
        $video->setAttribute('class', 'reddit-image');

        foreach ($details as $detail) {
            $source = $video->appendChild($dom->createElement('source'));
            $source->setAttribute('src', $detail['link']);
            $source->setAttribute('type', $detail['format']);
        }

        return $dom;
    }
}
