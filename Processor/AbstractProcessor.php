<?php

declare(strict_types=1);

namespace RedditImage\Processor;

use RedditImage\Settings;

abstract class AbstractProcessor {
    protected const MATCH_REDDIT = 'reddit.com';

    protected $settings;

    /** @var TransformerInterface[] */
    protected array $transformers = [];

    public function __construct(Settings $settings) {
        $this->settings = $settings;
        $this->settings->setProcessor(get_class($this));
    }

    /**
     * @param FreshRSS_Entry $entry
     * @return FreshRSS_Entry
     */
    abstract public function process($entry);

    protected function isRedditLink($entry): bool {
        return (bool) strpos($entry->link(), static::MATCH_REDDIT);
    }
}
