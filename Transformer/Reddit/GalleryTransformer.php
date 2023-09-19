<?php

declare(strict_types=1);

namespace RedditImage\Transformer\Reddit;

use RedditImage\Content;
use RedditImage\Media\Image;
use RedditImage\Transformer\AbstractTransformer;
use RedditImage\Transformer\TransformerInterface;

class GalleryTransformer extends AbstractTransformer implements TransformerInterface
{
    public function canTransform(Content $content): bool
    {
        return preg_match('#reddit.com/gallery#', $content->getContentLink()) === 1;
    }

    public function transform(Content $content): string
    {
        $images = [];
        $media = $this->getMediaMetadata($content);

        foreach ($media as $id => $metadata) {
            list(, $extension) = explode('/', $metadata['m']);
            $images[] = new Image("https://i.redd.it/{$id}.{$extension}");
            sleep($this->settings->getRedditDelay());
        }

        if ($images !== []) {
            $dom = $this->generateDom($images);

            return $dom->saveHTML() ?: '';
        }

        return '';
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getMediaMetadata(Content $content): array
    {
        $arrayResponse = $this->client->jsonGet("{$content->getCommentsLink()}.json");

        return $arrayResponse[0]['data']['children'][0]['data']['media_metadata']
            ?? $arrayResponse[0]['data']['children'][0]['data']['crosspost_parent_list'][0]['media_metadata']
            ?? [];
    }
}
