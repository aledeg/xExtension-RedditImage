<?php

declare(strict_types=1);

namespace RedditImage\Tests\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

class htmlHasLink extends Constraint
{
    private string $link;

    public function __construct(string $link)
    {
        $this->link = $link;
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
        $links = $xpath->query("body/div/p/a[@href='{$this->link}']");

        if ($links === false || $links->length !== 1) {
            return false;
        }

        return true;
    }

    public function toString(): string
    {
        return "has the link {$this->link}";
    }
}
