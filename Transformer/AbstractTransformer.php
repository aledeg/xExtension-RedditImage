<?php

declare(strict_types=1);

namespace RedditImage\Transformer;

use RedditImage\Media\DomElementInterface;
use RedditImage\Media\Image;
use RedditImage\Media\Video;

abstract class AbstractTransformer {
    protected const MATCH_REDDIT = 'reddit.com';

    /**
     * @param Entry $entry
     */
    protected function isRedditLink($entry): bool {
        return (bool) strpos($entry->link(), static::MATCH_REDDIT);
    }

    /**
     * @return Entry
     */
    abstract public function transform($entry);

    protected function getOriginComment(string $origin): string {
        $className = (new \ReflectionClass($this))->getShortName();

        return "xExtension-RedditImage | $className | $origin";
    }

    protected function generateDom(string $origin, array $media = []): \DomDocument {
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
