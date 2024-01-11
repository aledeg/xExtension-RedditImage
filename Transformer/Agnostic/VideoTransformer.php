<?php

declare(strict_types=1);

namespace RedditImage\Transformer\Agnostic;

use RedditImage\Content;
use RedditImage\Media\Video;
use RedditImage\Transformer\AbstractTransformer;
use RedditImage\Transformer\TransformerInterface;

class VideoTransformer extends AbstractTransformer implements TransformerInterface
{
    private const MATCHING_REGEX = '#(?P<baseurl>.+\.)(?P<extension>webm|mp4)$#';

    public function canTransform(Content $content): bool
    {
        return preg_match(self::MATCHING_REGEX, $content->getContentLink()) === 1;
    }

    public function transform(Content $content): string
    {
        $url = $content->getContentLink();
        preg_match(self::MATCHING_REGEX, $url, $matches);

        if (!$this->client->isAccessible($url)) {
            return '';
        }

        $extension = $matches['extension'];
        $dom = $this->generateDom([new Video("video/{$extension}", $url)]);

        return $dom->saveHTML() ?: '';
    }
}
