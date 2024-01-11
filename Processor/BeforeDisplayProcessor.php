<?php

declare(strict_types=1);

namespace RedditImage\Processor;

use Throwable;
use Minz_Log;
use RedditImage\Content;
use RedditImage\Exception\InvalidContentException;
use RedditImage\Settings;
use RedditImage\Transformer\Agnostic\ImageTransformer as AgnosticImageTransformer;
use RedditImage\Transformer\Agnostic\LinkTransformer as AgnosticLinkTransformer;
use RedditImage\Transformer\Agnostic\VideoTransformer as AgnosticVideoTransformer;
use RedditImage\Transformer\Imgur\ImageTransformer as ImgurImageTransformer;
use RedditImage\Transformer\Imgur\VideoTransformer as ImgurVideoTransformer;

class BeforeDisplayProcessor extends AbstractProcessor
{
    public function __construct(Settings $settings)
    {
        parent::__construct($settings);

        if ($this->settings->getDisplayImage()) {
            $this->transformers[] = new AgnosticImageTransformer($this->settings);
            $this->transformers[] = new ImgurVideoTransformer($this->settings);
            $this->transformers[] = new ImgurImageTransformer($this->settings);
        }
        if ($this->settings->getDisplayVideo()) {
            $this->transformers[] = new AgnosticVideoTransformer($this->settings);
        }
        $this->transformers[] = new AgnosticLinkTransformer($this->settings);
    }

    /**
     * @param \FreshRSS_Entry $entry
     * @return \FreshRSS_Entry
     */
    public function process($entry)
    {
        if (false === $this->isRedditLink($entry)) {
            return $entry;
        }

        try {
            $content = new Content($entry->content());
        } catch (InvalidContentException $exception) {
            Minz_Log::error($exception->__toString());
            return $entry;
        }

        $improved = $this->getImprovedContent($content);
        $original = $this->getOriginalContent($content);
        $metadata = $this->getMetadataContent($content);

        if (!$this->settings->getDisplayThumbnails()) {
            $entry->_attributes('thumbnail', null);
            $entry->_attributes('enclosures', null);
        }
        $entry->_content("{$improved}{$content->getReal()}{$original}{$metadata}");
        $entry->_link($content->getContentLink());

        return $entry;
    }

    private function getImprovedContent(Content $content): string
    {
        $improved = $content->hasBeenPreprocessed() ? $content->getPreprocessed() : $this->processContent($content);

        if ($improved === '') {
            return '';
        }

        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadHTML($improved, LIBXML_NOERROR);

        if (!$this->settings->getDisplayImage()) {
            $images = $dom->getElementsByTagName('img');
            // See https://www.php.net/manual/en/class.domnodelist.php#83390
            for ($i = $images->length; --$i >= 0;) {
                $image = $images->item($i);
                $image->parentNode->removeChild($image);
            }
        }

        if (!$this->settings->getDisplayVideo()) {
            $videos = $dom->getElementsByTagName('video');
            // See https://www.php.net/manual/en/class.domnodelist.php#83390
            for ($i = $videos->length; --$i >= 0;) {
                $video = $videos->item($i);
                $video->parentNode->removeChild($video);
            }
        }

        if ($this->settings->getMutedVideo()) {
            $videos = $dom->getElementsByTagName('video');
            foreach ($videos as $video) {
                $video->setAttribute('muted', 'true');
            }
            $audios = $dom->getElementsByTagName('audio');
            foreach ($audios as $audio) {
                $audio->setAttribute('muted', 'true');
            }
        }

        return $dom->saveHTML() ?: '';
    }

    private function processContent(Content $content): string
    {
        foreach ($this->transformers as $transformer) {
            if (!$transformer->canTransform($content)) {
                continue;
            }

            try {
                return $transformer->transform($content);
            } catch (Throwable $e) {
                Minz_Log::error("{$e->__toString()} - {$content->getContentLink()}");
            }
        }

        return '';
    }

    private function getOriginalContent(Content $content): string
    {
        if ($this->settings->getDisplayOriginal()) {
            return $content->getRaw();
        }

        return '';
    }

    private function getMetadataContent(Content $content): string
    {
        if ($this->settings->getDisplayMetadata()) {
            return "<div>{$content->getMetadata()}</div>";
        }

        return '';
    }
}
