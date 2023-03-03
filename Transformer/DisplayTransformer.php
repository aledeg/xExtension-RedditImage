<?php

declare(strict_types=1);

namespace RedditImage\Transformer;

use RedditImage\Content;
use RedditImage\Media\Image;
use RedditImage\Media\Video;

class DisplayTransformer extends AbstractTransformer {
    private bool $displayImage;
    private bool $displayVideo;
    private bool $mutedVideo;
    private bool $displayOriginal;
    private bool $displayMetadata;

    public function __construct(bool $displayImage, bool $displayVideo, bool $mutedVideo, bool $displayOriginal, bool $displayMetadata) {
        $this->displayImage = $displayImage;
        $this->displayVideo = $displayVideo;
        $this->mutedVideo = $mutedVideo;
        $this->displayOriginal = $displayOriginal;
        $this->displayMetadata = $displayMetadata;
    }

    /**
     * @param Entry $entry
     * @return Entry
     */
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

        $entry->_attributes('thumbnail', null);
        $entry->_attributes('enclosures', null);

        $entry->_content("{$improved}{$content->getReal()}{$original}{$metadata}");
        $entry->_link($content->getContentLink());

        return $entry;
    }

    private function getPreprocessedContent(Content $content): string {
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

        $dom = new \DomDocument('1.0', 'UTF-8');
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

    private function getImprovedContent(Content $content): string {
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
            $href = "{$href}.png";
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

    private function getNewImageContent(string $href, string $origin): ?string {
        if (!$this->displayImage) {
            return null;
        }

        $dom = $this->generateDom($origin, [new Image($href)]);
        return $dom->saveHTML();
    }

    private function getNewVideoContent(string $baseUrl, string $origin): ?string {
        if (!$this->displayVideo) {
            return null;
        }

        $mp4Url = "{$baseUrl}mp4";
        if ($this->isAccessible($mp4Url)) {
            $dom = $this->generateDom($origin, [new Video('video/mp4', $mp4Url)]);
            $videos = $dom->getElementsByTagName('video');
            foreach ($videos as $video) {
                $video->setAttribute('muted', 'true');
            }
            return $dom->saveHTML();
        }

        $webmUrl = "{$baseUrl}webm";
        if ($this->isAccessible($webmUrl)) {
            $dom = $this->generateDom($origin, [new Video('video/webm', $webmUrl)]);
            $videos = $dom->getElementsByTagName('video');
            foreach ($videos as $video) {
                $video->setAttribute('muted', 'true');
            }
            return $dom->saveHTML();
        }
    }

    private function isAccessible(string $href): bool {
        $channel = curl_init($href);
        curl_setopt($channel, CURLOPT_NOBODY, true);
        curl_exec($channel);
        $httpCode = curl_getinfo($channel, CURLINFO_HTTP_CODE);
        curl_close($channel);

        return 200 === $httpCode;
    }

    private function getNewLinkContent(string $href): string {
        $dom = new \DomDocument('1.0', 'UTF-8');

        $p = $dom->appendChild($dom->createElement('p'));
        $a = $p->appendChild($dom->createElement('a'));
        $a->setAttribute('href', $href);
        $a->textContent = $href;

        return $dom->saveHTML();
    }
}
