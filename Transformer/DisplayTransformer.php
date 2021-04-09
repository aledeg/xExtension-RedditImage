<?php

namespace RedditImage\Transformer;

use RedditImage\Content;

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

        $content = new Content($entry->content());

        if (null === $content->getContentLink()) {
            return $entry;
        }

        // Currently, only images are added during preprocessing.
        $preprocessed = $this->displayImage ? $content->getPreprocessed() : '';
        $improved = $content->hasBeenPreprocessed() ? '' : $this->getImprovedContent($content);
        $original = $this->displayOriginal ? $content->getRaw() : '';
        $metadata = $this->displayMetadata ? "<div>{$content->getMetadata()}</div>" : '';

        $entry->_content("{$preprocessed}{$improved}{$content->getReal()}{$original}{$metadata}");
        $entry->_link($content->getContentLink());

        return $entry;
    }

    /**
     * @param Content $href
     * @return string
     */
    private function getImprovedContent($content) {
        $href = $content->getContentLink();

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
        if (!$content->hasReal()) {
            return $this->getNewLinkContent($href);
        }
        return '';
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
}
