<?php

namespace RedditImage;

class Content {
    private $content;
    private $dom;
    private $preprocessed = '';
    private $metadata = '';
    private $contentLink;
    private $commentsLink;
    private $raw;
    private $real = '';

    public function __construct($content) {
        $this->content = $content;
        $this->raw = $content;

        $this->dom = new \DomDocument();
        $this->dom->loadHTML($content, LIBXML_NOERROR);

        $this->splitContent();
        $this->extractMetadata();
        $this->extractLinks();
        $this->extractReal();
    }

    public function getContentLink() {
        return $this->contentLink;
    }

    public function getCommentsLink() {
        return $this->commentsLink;
    }

    public function getPreprocessed() {
        return $this->preprocessed;
    }

    public function getMetadata() {
        return $this->metadata;
    }

    public function getRaw() {
        return $this->raw;
    }

    public function getReal() {
        return $this->real;
    }

    public function hasBeenPreprocessed() {
        return '' !== $this->preprocessed;
    }

    public function hasReal() {
        return '' !== $this->real;
    }

    /**
     * Split the content when needed
     *
     * The content can be preprocessed to save time for resources that can not be
     * fetch quickly. For instance when API calls are involved. Thus we need to
     * separate the feed raw content from the preprocessed content.
     */
    private function splitContent() {
        $xpath = new \DOMXpath($this->dom);
        $redditImage = $xpath->query("//div[contains(@class,'reddit-image')]");

        if (1 === $redditImage->length) {
            $node = $redditImage->item(0);
            $this->preprocessed = $this->dom->saveHTML($node);
            $node->parentNode->removeChild($node);
            $this->raw = $this->dom->saveHTML();
        }
    }

    /**
     * Extract metadata available in the feed raw content
     *
     * Here the search is done with a regex instead of the DOM since the raw content
     * has different ways to represent its content.
     */
    private function extractMetadata() {
        if (preg_match('#(?P<metadata>submitted.*</span>)#', $this->content, $matches)) {
            $this->metadata = $matches['metadata'];
        }
    }

    /**
     * Extract links available in the feed raw content
     *
     * At the moment, those are the extracted links:
     *   - content link.
     *   - comments link.
     */
    private function extractLinks() {
        $links = $this->dom->getElementsByTagName('a');
        foreach ($links as $link) {
            switch ($link->textContent) {
                case '[link]':
                    $this->contentLink = $link->getAttribute('href');
                    break;
                case '[comments]':
                    $this->commentsLink = $link->getAttribute('href');
                default:
                    break;
            }
        }
    }

    /**
     * Extract the real content from the feed raw content
     *
     * When the raw content does not contain a table, that means it has some
     * real content (most of the time, it's text).
     */
    private function extractReal() {
        $nodes = $this->dom->getElementsByTagName('table');
        if (0 === $nodes->length) {
            $this->real = str_replace($this->metadata, '', $this->raw);
        }
    }
}