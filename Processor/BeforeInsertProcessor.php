<?php

declare(strict_types=1);

namespace RedditImage\Processor;

use Throwable;
use Minz_Log;
use RedditImage\Client\Client;
use RedditImage\Content;
use RedditImage\Exception\InvalidContentException;
use RedditImage\Settings;
use RedditImage\Transformer\Agnostic\ImageTransformer as AgnosticImageTransformer;
use RedditImage\Transformer\Flickr\ImageTransformer as FlickrImageTransformer;
use RedditImage\Transformer\Imgur\GalleryWithClientIdTransformer as ImgurGalleryWithClientIdTransformer;
use RedditImage\Transformer\Imgur\ImageTransformer as ImgurImageTransformer;
use RedditImage\Transformer\Imgur\VideoTransformer as ImgurVideoTransformer;
use RedditImage\Transformer\Reddit\GalleryTransformer as RedditGalleryTransformer;
use RedditImage\Transformer\Reddit\VideoTransformer as RedditVideoTransformer;

class BeforeInsertProcessor extends AbstractProcessor
{
    public function __construct(Settings $settings, Client $client)
    {
        parent::__construct($settings);

        $this->transformers[] = new AgnosticImageTransformer($this->settings);
        $this->transformers[] = new ImgurGalleryWithClientIdTransformer($this->settings);
        $this->transformers[] = new ImgurImageTransformer($this->settings);
        $this->transformers[] = new ImgurVideoTransformer($this->settings);
        $this->transformers[] = new RedditVideoTransformer($this->settings);
        $this->transformers[] = new RedditGalleryTransformer($this->settings);
        $this->transformers[] = new FlickrImageTransformer($this->settings);

        foreach ($this->transformers as $transformer) {
            $transformer->setClient($client);
        }
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

        $newContent = '';
        try {
            $content = new Content($entry->content());
        } catch (InvalidContentException $exception) {
            Minz_Log::error($exception->__toString());
            return $entry;
        }

        foreach ($this->transformers as $transformer) {
            if (!$transformer->canTransform($content)) {
                continue;
            }

            try {
                $newContent = $transformer->transform($content);
                if ($newContent !== '') {
                    $entry->_content("{$newContent}{$content->getRaw()}");
                }
                break;
            } catch (Throwable $e) {
                Minz_Log::error("{$e->__toString()} - {$content->getContentLink()}");
            }
        }

        return $entry;
    }
}
