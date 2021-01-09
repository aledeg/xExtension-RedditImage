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
     * @return string|null
     */
    protected function extractOriginalContentLink($entry) {
        if (preg_match('#<a href="(?P<href>[^"]*)">\[link\]</a>#', $entry->content(), $matches)) {
            return $matches['href'];
        }
    }

    /**
     * @return string|null
     */
    protected function extractOriginalCommentsLink($entry) {
        if (preg_match('#<a href="(?P<href>[^"]*)">\[comments\]</a>#', $entry->content(), $matches)) {
            return $matches['href'];
        }
    }

    /**
     * @return object
     */
    abstract public function transform($entry);
}
