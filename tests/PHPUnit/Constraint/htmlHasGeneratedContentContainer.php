<?php

declare(strict_types=1);

namespace RedditImage\Tests\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

class htmlHasGeneratedContentContainer extends Constraint
{
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
        $container = $xpath->query("body/div[@class='reddit-image figure']");
        if ($container === false || $container->length !== 1) {
            return false;
        }

        $comment = $container->item(0)->firstChild;
        if ($comment->nodeType !== XML_COMMENT_NODE) {
            return false;
        }

        return preg_match('#xExtension-RedditImage/\w+ \| \w+ \| RedditImage\\\\Transformer\\\\.+Transformer#', $comment->textContent) === 1;
    }

    public function toString(): string
    {
        return 'has a content container';
    }
}
