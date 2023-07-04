<?php

declare(strict_types=1);

namespace RedditImage\Media;

interface DomElementInterface
{
    public function toDomElement(\DomDocument $domDocument): \DomElement;
}
