<?php

declare(strict_types=1);

namespace RedditImage\Transformer;

use RedditImage\Client\Client;
use RedditImage\Content;

interface TransformerInterface
{
    public function canTransform(Content $content): bool;

    public function transform(Content $content): string;

    public function setClient(Client $client): void;
}
