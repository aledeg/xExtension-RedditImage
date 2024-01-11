<?php

declare(strict_types=1);

namespace RedditImage\Transformer\Agnostic;

use RedditImage\Content;
use RedditImage\Media\Image;
use RedditImage\Transformer\AbstractTransformer;
use RedditImage\Transformer\TransformerInterface;

class ImageTransformer extends AbstractTransformer implements TransformerInterface
{
    /** @var string[] */
    private array $supportedFormats = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'bmp',
    ];

    /** @var string[] */
    private array $blacklist = [
        'redgifs.com',
    ];

    public function canTransform(Content $content): bool
    {
        return preg_match($this->generateBlacklistRegex(), $content->getContentLink()) !== 1 &&
            preg_match($this->generateMatchingRegex(), $content->getContentLink()) === 1;
    }

    public function transform(Content $content): string
    {
        preg_match($this->generateMatchingRegex(), $content->getContentLink(), $matches);
        $dom = $this->generateDom([new Image($matches['link'])]);

        return $dom->saveHTML() ?: '';
    }

    private function generateMatchingRegex(): string
    {
        return '#(?P<link>.*\.('.implode('|', $this->supportedFormats).'))(\?.*)?$#';
    }

    private function generateBlacklistRegex(): string
    {
        return '#('.implode('|', $this->blacklist).')#';
    }
}
