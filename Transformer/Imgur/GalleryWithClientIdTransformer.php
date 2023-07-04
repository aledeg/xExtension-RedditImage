<?php

declare(strict_types=1);

namespace RedditImage\Transformer\Imgur;

use RedditImage\Content;
use RedditImage\Media\Image;
use RedditImage\Media\Video;
use RedditImage\Transformer\AbstractTransformer;
use RedditImage\Transformer\TransformerInterface;

class GalleryWithClientIdTransformer extends AbstractTransformer implements TransformerInterface
{
    private const MATCHING_REGEX = '@imgur.com/a/(?P<imageHash>\w+)@';

    public function canTransform(Content $content): bool
    {
        return $this->settings->hasImgurClientId() && preg_match(self::MATCHING_REGEX, $content->getContentLink()) === 1;
    }

    public function transform(Content $content): string
    {
        $gallery = [];
        $media = $this->getMediaMetadata($content);

        foreach ($media as $medium) {
            if (false !== strpos($medium['type'], 'video')) {
                $gallery[] = new Video($medium['type'], $medium['link']);
            } elseif (false !== strpos($medium['type'], 'image')) {
                $gallery[] = new Image($medium['link']);
            }
        }

        if ($gallery !== []) {
            $dom = $this->generateDom($gallery);

            return $dom->saveHTML() ?: '';
        }

        return '';
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getMediaMetadata(Content $content): array
    {
        preg_match(self::MATCHING_REGEX, $content->getContentLink(), $matches);
        $arrayResponse = $this->client->jsonGet("https://api.imgur.com/3/album/{$matches['imageHash']}/images", [
            "Authorization: Client-ID {$this->settings->getImgurClientId()}",
        ]);

        return $arrayResponse['data'] ?? [];
    }
}
