<?php

namespace RedditImage\Transformer;

class DisplayTransformer extends AbstractTransformer {
    private $displayImage;
    private $displayVideo;
    private $mutedVideo;
    private $displayOriginal;
    private $displayMetadata;

    public function __construct(bool $displayImage, bool $displayVideo, bool $mutedVideo, bool $displayOriginal, bool $displayMetadata) {
        $this->displayImage = $displayImage;
        $this->displayVideo = $displayVideo;
        $this->mutedVideo = $mutedVideo;
        $this->displayOriginal = $displayOriginal;
        $this->displayMetadata = $displayMetadata;
    }

    public function transform($entry) {
        if (false === $this->isRedditLink($entry)) {
            return $entry;
        }

        if (null === $href = $this->extractOriginalContentLink($entry)) {
            return $entry;
        }

        $content = $this->getImprovedContent($href);
        $content .= $this->getStrippedContent($entry->content());
        $content .= $this->extractMetadata($entry->content());

        $entry->_content($content);
        $entry->_link($href);

        return $entry;
    }

    /**
     * @param string $href
     * @return string
     */
    private function getImprovedContent($href) {
        // Add image tag in content when the href links to an image
        if (preg_match('#(jpg|png|gif|bmp)(\?.*)?$#', $href)) {
            return $this->getNewImageContent($href);
        }
        // Add video tag in content when the href links to an imgur gifv
        if (preg_match('#(?P<gifv>.*imgur.com/[^/]*.)gifv$#', $href, $matches)) {
            return $this->getNewVideoContent($matches['gifv']);
        }
        // Add image tag in content when the href links to an imgur image
        if (preg_match('#(?P<imgur>imgur.com/[^/]*)$#', $href)) {
            $href = "${href}.png";
            return $this->getNewImageContent($href);
        }
        // Add video tag in content when the href links to a video
        if (preg_match('#(?P<baseurl>.+)(webm|mp4)$#', $href, $matches)) {
            return $this->getNewVideoContent($matches['baseurl']);
        }
        return $this->getNewLinkContent($href);
    }

    /**
     * @return string|null
     */
    private function getNewImageContent($href) {
        if (!$this->displayImage) {
            return;
        }

        return <<<CONTENT
<div class="reddit-image figure">
    <img src="{$href}" class="reddit-image"/>
</div>
CONTENT;
    }

    /**
     * @return string|null
     */
    private function getNewVideoContent($baseUrl) {
        if (!$this->displayVideo) {
            return;
        }
        $muted = $this->mutedVideo ? 'muted' : '';

        return <<<CONTENT
<div class="reddit-image figure">
    <video controls preload="metadata" {$muted} class="reddit-image">
        <source src="{$baseUrl}webm" type="video/webm">
        <source src="{$baseUrl}mp4" type="video/mp4">
    </video>
</div>
CONTENT;
    }

    /**
     * @return string
     */
    private function getNewLinkContent($href) {
        return <<<CONTENT
<p>
    <a href="{$href}">{$href}</a>
</p>
CONTENT;
    }

    /**
     * Get the stripped content of the entry.
     *
     * As the content might be modified upon insertion, it's mandatory to check the content
     * for the original HTML table
     *
     * @return string
     */
    private function getStrippedContent($content) {
        if ($this->displayOriginal) {
            return $content;
        }

        $dom = new \DomDocument();
        $dom->loadHTML($content);

        if (null === $tableNode = $dom->getElementsByTagName('table')->item(0)) {
            return $content;
        }

        $tableNode->parentNode->removeChild($tableNode);

        return $dom->saveHTML();
    }

    /**
     * Extract metadata from original article
     *
     * @param string $content
     * @return string
     */
    private function extractMetadata($content) {
        if (!$this->displayMetadata) {
            return '';
        }

        $dom = new \DOMDocument();
        $dom->loadHTML($content);
        if (null === $metadataNode = $dom->getElementsByTagName('td')->item(0)) {
            return $content;
        }

        $metadataNode->parentNode->removeChild($metadataNode);

        return $dom->saveHTML();
    }
}
