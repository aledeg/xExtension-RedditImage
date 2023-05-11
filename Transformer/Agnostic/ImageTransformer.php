<?php

declare(strict_types=1);

namespace RedditImage\Transformer\Agnostic;

use RedditImage\Content;
use RedditImage\Media\Image;
use RedditImage\Transformer\AbstractTransformer;
use RedditImage\Transformer\TransformerInterface;

class ImageTransformer extends AbstractTransformer implements TransformerInterface {
    private const MATCHING_REGEX = '#(?P<link>.*\.(jpg|jpeg|png|gif|bmp))(\?.*)?$#';

    public function canTransform(Content $content): bool {
        return preg_match(self::MATCHING_REGEX, $content->getContentLink()) === 1;
    }

    public function transform(Content $content): string {
        preg_match(self::MATCHING_REGEX, $content->getContentLink(), $matches);
        $dom = $this->generateDom([new Image($matches['link'])]);

        return $dom->saveHTML();
    }
}
