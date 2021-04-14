<?php

namespace RedditImage\Transformer;

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
     * @param string $origin
     * @param array $links
     * @return \DomDocument
     */
    protected function generateImageDom($origin, $links = []) {
        $className = (new \ReflectionClass($this))->getShortName();

        $dom = new \DomDocument();

        $div = $dom->appendChild($dom->createElement('div'));
        $div->setAttribute('class', 'reddit-image figure');

        $div->appendChild($dom->createComment("xExtension-RedditImage | $className | $origin"));

        foreach ($links as $link) {
            $img = $div->appendChild($dom->createElement('img'));
            $img->setAttribute('src', $link);
            $img->setAttribute('class', 'reddit-image');
        }

        return $dom;
    }
}
