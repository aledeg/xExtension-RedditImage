<?php

declare(strict_types=1);

namespace RedditImage\Transformer\Imgur;

use RedditImage\Content;
use RedditImage\Media\Image;
use RedditImage\Transformer\AbstractTransformer;
use RedditImage\Transformer\TransformerInterface;

class ImageTransformer extends AbstractTransformer implements TransformerInterface
{
    public function canTransform(Content $content): bool
    {
        return preg_match('#(imgur.com/[^/?.]+)$#', $content->getContentLink()) === 1;
    }

    public function transform(Content $content): string
    {
        $dom = $this->generateDom([new Image("{$content->getContentLink()}.png")]);

        return $dom->saveHTML() ?: '';
    }
}
