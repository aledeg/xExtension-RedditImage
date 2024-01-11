<?php

declare(strict_types=1);

namespace RedditImage\Transformer\Agnostic;

use RedditImage\Content;
use RedditImage\Media\Link;
use RedditImage\Transformer\AbstractTransformer;
use RedditImage\Transformer\TransformerInterface;

class LinkTransformer extends AbstractTransformer implements TransformerInterface
{
    public function canTransform(Content $content): bool
    {
        return !$content->hasReal();
    }

    public function transform(Content $content): string
    {
        $dom = $this->generateDom([new Link($content->getContentLink())]);

        return $dom->saveHTML() ?: '';
    }
}
