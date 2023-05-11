<?php

declare(strict_types=1);

namespace RedditImage\Media;

class Link implements DomElementInterface {
    private string $url;

    public function __construct(string $url) {
        $this->url = $url;
    }

    public function toDomElement(\DomDocument $domDocument): \DomElement {
        $p = $domDocument->createElement('p');
        $a = $p->appendChild($domDocument->createElement('a'));
        $a->setAttribute('href', $this->url);
        $a->appendChild($domDocument->createTextNode($this->url));

        return $p;
    }
}
