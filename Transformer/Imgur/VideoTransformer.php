<?php

declare(strict_types=1);

namespace RedditImage\Transformer\Imgur;

use RedditImage\Content;
use RedditImage\Media\Video;
use RedditImage\Transformer\AbstractTransformer;
use RedditImage\Transformer\TransformerInterface;

class VideoTransformer extends AbstractTransformer implements TransformerInterface
{
    private const MATCHING_REGEX = '#(?P<gifv>.*imgur.com/[^/]*.)gifv$#';

    public function canTransform(Content $content): bool
    {
        return preg_match(self::MATCHING_REGEX, $content->getContentLink()) === 1;
    }

    public function transform(Content $content): string
    {
        preg_match(self::MATCHING_REGEX, $content->getContentLink(), $matches);
        $dom = $this->generateDom([new Video('video/mp4', "{$matches['gifv']}mp4")]);

        return $dom->saveHTML() ?: '';
    }
}
