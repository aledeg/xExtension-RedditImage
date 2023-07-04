<?php

declare(strict_types=1);

namespace RedditImage\Transformer\Flickr;

use RedditImage\Client\Client;
use RedditImage\Content;
use RedditImage\Media\Image;
use RedditImage\Transformer\AbstractTransformer;
use RedditImage\Transformer\TransformerInterface;

class ImageTransformer extends AbstractTransformer implements TransformerInterface
{
    private const MATCHING_REGEX = '#flickr.com/photos/\w+@\w+/(?P<photoId>\w+)#';

    public function canTransform(Content $content): bool
    {
        return $this->settings->hasFlickrApiKey() && preg_match(self::MATCHING_REGEX, $content->getContentLink()) === 1;
    }

    public function transform(Content $content): string
    {
        $media = $this->getMediaMetadata($content);

        if ($media === []) {
            return '';
        }

        $largestImage = array_pop($media)['source'] ?? '';
        if ($largestImage === '') {
            return '';
        }

        $dom = $this->generateDom([new Image($largestImage)]);

        return $dom->saveHTML() ?: '';
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function getMediaMetadata(Content $content): array
    {
        preg_match(self::MATCHING_REGEX, $content->getContentLink(), $matches);
        $arrayResponse = $this->client->jsonGet(
            "https://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key={$this->settings->getFlickrApiKey()}&photo_id={$matches['photoId']}&format=json",
            [],
            static function (string $payload) {
                return preg_replace(['/^jsonFlickrApi\(/', '/\)$/'], '', $payload);
            }
        );

        return $arrayResponse['sizes']['size'] ?? [];
    }
}
