<?php

declare(strict_types=1);

namespace RedditImage\Media;

class Image implements DomElementInterface
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function toDomElement(\DomDocument $domDocument): \DomElement
    {
        $image = $domDocument->createElement('img');
        $image->setAttribute('src', $this->url);
        $image->setAttribute('class', 'reddit-image');

        return $image;
    }
}
