<?php

namespace RedditImage\Transformer;

use RedditImage\Media\Image;
use RedditImage\Media\Video;

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
    protected function generateDom($origin, $media = []) {
        $dom = new \DomDocument();

        $div = $dom->appendChild($dom->createElement('div'));
        $div->setAttribute('class', 'reddit-image figure');

        $div->appendChild($dom->createComment($this->getOriginComment($origin)));

        foreach ($media as $medium) {
            if ($medium instanceof Image) {
                $img = $div->appendChild($dom->createElement('img'));
                $img->setAttribute('src', $medium->getUrl());
                $img->setAttribute('class', 'reddit-image');
            } elseif ($medium instanceof Video) {
                $video = $div->appendChild($dom->createElement('video'));
                $video->setAttribute('controls', true);
                $video->setAttribute('preload', 'metadata');
                $video->setAttribute('class', 'reddit-image');

                if ($medium->hasAudioTrack()) {
                    $audio = $video->appendChild($dom->createElement('audio'));
                    $audio->setAttribute('controls', true);
                    $source = $audio->appendChild($dom->createElement('source'));
                    $source->setAttribute('src', $medium->getAudioTrack());
                }

                foreach ($medium->getSources() as $format => $url) {
                    $source = $video->appendChild($dom->createElement('source'));
                    $source->setAttribute('src', $url);
                    $source->setAttribute('type', $format);
                }
            }
        }

        return $dom;
    }
}
