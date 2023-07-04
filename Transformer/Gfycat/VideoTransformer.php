<?php

declare(strict_types=1);

namespace RedditImage\Transformer\Gfycat;

use RedditImage\Client\Client;
use RedditImage\Content;
use RedditImage\Media\Video;
use RedditImage\Transformer\AbstractTransformer;
use RedditImage\Transformer\TransformerInterface;

class VideoTransformer extends AbstractTransformer implements TransformerInterface
{
    private const MATCHING_REGEX = '#(gfycat.com/)(.*/)*(?P<token>\w+)$#';

    public function canTransform(Content $content): bool
    {
        return preg_match(self::MATCHING_REGEX, $content->getContentLink()) === 1;
    }

    public function transform(Content $content): string
    {
        $arrayResponse = $this->getMediaMetadata($content);

        $mp4Url = $arrayResponse['gfyItem']['mp4Url'] ?? '';
        $webmUrl = $arrayResponse['gfyItem']['webmUrl'] ?? '';
        if ($mp4Url === '' || $webmUrl === '') {
            return '';
        }

        $video = new Video('video/mp4', $mp4Url);
        $video->addSource('video/webm', $webmUrl);

        $dom = $this->generateDom([$video]);

        return $dom->saveHTML() ?: '';
    }

    /**
     * @return array<int|string, mixed>
     */
    private function getMediaMetadata(Content $content): array
    {
        preg_match(self::MATCHING_REGEX, $content->getContentLink(), $matches);

        return $this->client->jsonGet("https://api.gfycat.com/v1/gfycats/{$matches['token']}");
    }
}
