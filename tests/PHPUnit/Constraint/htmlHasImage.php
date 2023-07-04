<?php

declare(strict_types=1);

namespace RedditImage\Tests\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

class htmlHasImage extends Constraint
{
    private string $imageUrl;

    public function __construct(string $imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @param mixed $other
     */
    public function matches($other): bool
    {
        if (!is_string($other)) {
            return false;
        }

        $dom = new \DomDocument('1.0', 'UTF-8');
        if ($dom->loadHTML($other, LIBXML_NOERROR) === false) {
            return false;
        }

        $xpath = new \DOMXpath($dom);
        $images = $xpath->query("body/div/img[@class='reddit-image'][@src='{$this->imageUrl}']");

        if ($images === false || $images->length !== 1) {
            return false;
        }

        return true;
    }

    public function toString(): string
    {
        return "has the image with {$this->imageUrl} source";
    }
}
