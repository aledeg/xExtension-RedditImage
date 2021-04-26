<?php

namespace RedditImage\Transformer;

use RedditImage\Content;
use RedditImage\Media\Image;
use RedditImage\Media\Video;

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

        $improved = $content->hasBeenPreprocessed() ? $this->getPreprocessedContent($content) : $this->getImprovedContent($content);
        $original = $this->displayOriginal ? $content->getRaw() : '';
        $metadata = $this->displayMetadata ? "<div>{$content->getMetadata()}</div>" : '';

        $entry->_content("{$improved}{$content->getReal()}{$original}{$metadata}");
        $entry->_link($content->getContentLink());

        return $entry;
    }

    /**
     * @param Content $content
     * @return string
     */
    private function getPreprocessedContent($content) {
        $preprocessed = $content->getPreprocessed();
        if (!$this->displayImage && false !== strpos($preprocessed, 'img')) {
            return '';
        }
        if (!$this->displayVideo && false !== strpos($preprocessed, 'video')) {
            return '';
        }
        if (false === strpos($preprocessed, 'video')) {
            return $preprocessed;
        }
        if (!$this->mutedVideo) {
            return $preprocessed;
        }

        $dom = new \DomDocument();
        $dom->loadHTML($preprocessed, LIBXML_NOERROR);

        $videos = $dom->getElementsByTagName('video');
        foreach ($videos as $video) {
            $video->setAttribute('muted', true);
        }
        $audios = $dom->getElementsByTagName('audio');
        foreach ($audios as $audio) {
            $audio->setAttribute('muted', true);
        }

        return $dom->saveHTML();
    }

    /**
     * @param Content $content
     * @return string
     */
    private function getImprovedContent($content) {
        $href = $content->getContentLink();

        // Add image tag in content when the href links to an image
        if (preg_match('#(jpg|png|gif|bmp)(\?.*)?$#', $href)) {
            return $this->getNewImageContent($href, 'Image link');
        }
        // Add video tag in content when the href links to an imgur gifv
        if (preg_match('#(?P<gifv>.*imgur.com/[^/]*.)gifv$#', $href, $matches)) {
            return $this->getNewVideoContent($matches['gifv'], 'Imgur gifv');
        }
        // Add image tag in content when the href links to an imgur image
        if (preg_match('#(?P<imgur>imgur.com/[^/]*)$#', $href)) {
            $href = "${href}.png";
            return $this->getNewImageContent($href, 'Imgur token');
        }
        // Add video tag in content when the href links to a video
        if (preg_match('#(?P<baseurl>.+)(webm|mp4)$#', $href, $matches)) {
            return $this->getNewVideoContent($matches['baseurl'], 'Video link');
        }
        if (!$content->hasReal()) {
            return $this->getNewLinkContent($href);
        }
        return '';
    }

    /**
     * @return string|null
     */
    private function getNewImageContent($href, $origin) {
        if (!$this->displayImage) {
            return;
        }

        $dom = $this->generateDom($origin, [new Image($href)]);
        return $dom->saveHTML();
    }

    /**
     * @return string|null
     */
    private function getNewVideoContent($baseUrl, $origin) {
        if (!$this->displayVideo) {
            return;
        }

        $mp4Url = "{$baseUrl}mp4";
        if ($this->isAccessible($mp4Url)) {
            $dom = $this->generateDom($origin, [new Video('video/mp4', $mp4Url)]);
            $videos = $dom->getElementsByTagName('video');
            foreach ($videos as $video) {
                $video->setAttribute('muted', true);
            }
            return $dom->saveHTML();
        }

        $webmUrl = "{$baseUrl}webm";
        if ($this->isAccessible($webmUrl)) {
            $dom = $this->generateDom($origin, [new Video('video/webm', $webmUrl)]);
            $videos = $dom->getElementsByTagName('video');
            foreach ($videos as $video) {
                $video->setAttribute('muted', true);
            }
            return $dom->saveHTML();
        }
    }

    /**
     * @return bool
     */
    private function isAccessible($href) {
        $channel = curl_init($href);
        curl_setopt($channel, CURLOPT_NOBODY, true);
        curl_exec($channel);
        $httpCode = curl_getinfo($channel, CURLINFO_HTTP_CODE);
        curl_close($channel);

        return 200 === $httpCode;
    }

    /**
     * @return string
     */
    private function getNewLinkContent($href) {
        $dom = new \DomDocument();

        $p = $dom->appendChild($dom->createElement('p'));
        $a = $p->appendChild($dom->createElement('a'));
        $a->setAttribute('href', $href);
        $a->textContent = $href;

        return $dom->saveHTML();
    }
}
