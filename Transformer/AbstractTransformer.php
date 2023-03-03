<?php

declare(strict_types=1);

namespace RedditImage\Transformer;

use RedditImage\Media\DomElementInterface;
use RedditImage\Media\Image;
use RedditImage\Media\Video;

abstract class AbstractTransformer {
    protected const MATCH_REDDIT = 'reddit.com';

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
        $dom = new \DomDocument('1.0', 'UTF-8');

        $div = $dom->appendChild($dom->createElement('div'));
        $div->setAttribute('class', 'reddit-image figure');

        $div->appendChild($dom->createComment($this->getOriginComment($origin)));

        foreach ($media as $medium) {
            if ($medium instanceof DomElementInterface) {
                $div->appendChild($medium->toDomElement($dom));
            }
        }

        return $dom;
    }
}
