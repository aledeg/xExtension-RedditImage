<?php

class RedditImageExtension extends Minz_Extension {
	public function init() {
		$this->registerHook('entry_before_display', array($this, 'transformLink'));
	}

	public function transformLink($entry) {
		$link = $entry->link();

		if (false === strpos($link, 'reddit.com')) {
			return $entry;
		}

		$content = $entry->content();

		// Change entry link by content link
		if (preg_match('#<a href="(?P<href>[^"]*)">\[link\]</a>#', $content, $matches)) {
			$href = $matches['href'];
			$entry->_link($href);
		}

		// Add image tag in content when the href links to an image
		if (preg_match('#(jpg|png|gif|bmp)(\?.*)?$#', $href)) {
			$entry->_content(sprintf('%s <img src="%s" class="reddit-image" alt="URL" />', $content, $href));
		// Add image tag in content when the href links to an imgur image
		} elseif (preg_match('#(?P<imgur>imgur.com/[^/]*)$#', $href)) {
			$entry->_content(sprintf('%s <img src="%s.png" class="reddit-image alt="IMGUR" />', $content, $href));
		} else {
			$entry->_content(sprintf('%s <p>%s</p>', $content, $href));
		}

		return $entry;
	}
}
