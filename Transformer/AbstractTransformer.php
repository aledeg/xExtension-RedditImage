<?php

declare(strict_types=1);

namespace RedditImage\Transformer;

use RedditImage\Client\Client;
use RedditImage\Media\DomElementInterface;
use RedditImage\Media\Image;
use RedditImage\Media\Video;
use RedditImage\Settings;

abstract class AbstractTransformer
{
    protected const MATCH_REDDIT = 'reddit.com';

    protected Client $client;
    protected Settings $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param \FreshRSS_Entry $entry
     */
    protected function isRedditLink($entry): bool
    {
        return (bool) strpos($entry->link(), static::MATCH_REDDIT);
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    protected function getOriginComment(): string
    {
        return sprintf(
            'xExtension-RedditImage/%s | %s | %s',
            REDDITIMAGE_VERSION,
            $this->settings->getProcessor(),
            get_class($this)
        );
    }

    /**
     * @param mixed[] $media
     */
    protected function generateDom(array $media = []): \DomDocument
    {
        $dom = new \DomDocument('1.0', 'UTF-8');

        $div = $dom->createElement('div');
        $div->setAttribute('class', 'reddit-image figure');

        $div->appendChild($dom->createComment($this->getOriginComment()));

        foreach ($media as $medium) {
            if ($medium instanceof DomElementInterface) {
                $div->appendChild($medium->toDomElement($dom));
            }
        }
        $dom->appendChild($div);

        return $dom;
    }
}
