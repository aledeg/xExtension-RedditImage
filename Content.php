<?php

declare(strict_types=1);

namespace RedditImage;

use RedditImage\Exception\InvalidContentException;

class Content
{
    private string $preprocessed = '';
    private string $metadata = '';
    private ?string $contentLink = null;
    private ?string $commentsLink = null;
    private string $raw;
    private string $real = '';

    public function __construct(string $content)
    {
        $this->raw = $content;

        $this->splitContent($content);
        $this->extractMetadata();
        $this->extractLinks();
        $this->extractReal();

        if (!$this->isValid()) {
            throw new InvalidContentException($content);
        }
    }

    private function isValid(): bool
    {
        if ($this->metadata === '') {
            return false;
        }
        if ($this->contentLink === null) {
            return false;
        }
        if ($this->commentsLink === null) {
            return false;
        }
        return true;
    }

    public function getContentLink(): ?string
    {
        return $this->contentLink;
    }

    public function getCommentsLink(): ?string
    {
        return $this->commentsLink;
    }

    public function getPreprocessed(): string
    {
        return $this->preprocessed;
    }

    public function getMetadata(): string
    {
        return $this->metadata;
    }

    public function getRaw(): string
    {
        return $this->raw;
    }

    public function getReal(): string
    {
        return $this->real;
    }

    public function hasBeenPreprocessed(): bool
    {
        return '' !== $this->preprocessed;
    }

    public function hasReal(): bool
    {
        return '' !== $this->real;
    }

    /**
     * Split the content when needed
     *
     * The content can be preprocessed to save time for resources that can not be
     * fetch quickly. For instance when API calls are involved. Thus we need to
     * separate the feed raw content from the preprocessed content.
     */
    private function splitContent(string $content): void
    {
        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            htmlspecialchars_decode(htmlentities(html_entity_decode($content))),
            LIBXML_NOERROR
        );

        $xpath = new \DOMXpath($dom);
        $redditImage = $xpath->query("//div[contains(@class,'reddit-image')]");

        if ($redditImage !== false && $redditImage->length === 1) {
            $node = $redditImage->item(0);
            $this->preprocessed = $dom->saveHTML($node->parentNode->firstChild) ?: '';
            $this->raw = $dom->saveHTML($node->parentNode->lastChild) ?: '';
        }
    }

    /**
     * Extract metadata available in the feed raw content
     *
     * Here the search is done with a regex instead of the DOM since the raw content
     * has different ways to represent its content. The metadata contains the link
     * to the author page, the link to the current message, and the link to the
     * current message comment section.
     */
    private function extractMetadata(): void
    {
        if (preg_match('#(?P<metadata>\s*?submitted.*</span>)#', $this->raw, $matches)) {
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
    private function extractLinks(): void
    {
        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            htmlspecialchars_decode(htmlentities(html_entity_decode($this->raw))),
            LIBXML_NOERROR
        );

        $links = $dom->getElementsByTagName('a');
        foreach ($links as $link) {
            switch ($link->textContent) {
                case '[link]':
                    $this->contentLink = $link->getAttribute('href');
                    break;
                case '[comments]':
                    $this->commentsLink = $link->getAttribute('href');
                    // no break
                default:
                    break;
            }
        }
    }

    /**
     * Extract the real content from the feed raw content
     *
     * The real content is contained in a div with the md class attribute. The
     * class attribute is sanitized to data-sanitized-class attribute when
     * processed by SimplePie.
     */
    private function extractReal(): void
    {
        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            htmlspecialchars_decode(htmlentities(html_entity_decode($this->raw))),
            LIBXML_NOERROR
        );

        $xpath = new \DOMXpath($dom);
        $mdNode = $xpath->query("//div[contains(@data-sanitized-class,'md')]");
        if ($mdNode !== false && $mdNode->length === 1) {
            $node = $mdNode->item(0);
            $this->real = $dom->saveHTML($node) ?: '';
        }
    }
}
