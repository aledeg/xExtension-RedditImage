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
			$entry->_content(sprintf('<img src="%2$s" class="reddit-image" alt="IMAGE URL" /> %1$s', $content, $href));
		// Add image tag in content when the href links to an imgur gifv
		} elseif (preg_match('#(?P<gifv>.*imgur.com/[^/]*).gifv$#', $href, $matches)) {
			$entry->_content(sprintf('<img src="%2$s.gif" class="reddit-image" alt="IMGUR GIFV" /> %1$s', $content, $matches['gifv']));
		// Add image tag in content when the href links to an imgur image
		} elseif (preg_match('#(?P<imgur>imgur.com/[^/]*)$#', $href)) {
			$entry->_content(sprintf('<img src="%2$s.png" class="reddit-image" alt="IMGUR TOKEN" /> %1$s', $content, $href));
		// Add video tag in content when the href links to a video
		} elseif (preg_match('#(?P<extension>webm|mp4)$#', $href, $matches)) {
			$entry->_content(sprintf('<video controls class="reddit-image"><source src="%2$s" type="video/%3$s">VIDEO URL</video> %1$s', $content, $href, $matches['extension']));
		// Add video tag in content when the href links to a gfycat video
		} elseif (preg_match('#(?P<gfycat>gfycat.com/)(.*/)*(?P<token>[^/.]*)$#', $href, $matches)) {
			$entry->_content(sprintf('<video controls class="reddit-image"><source src="https://giant.%2$s%3$s.mp4" type="video/mp4">GFYCAT TOKEN</video> %1$s', $content, $matches['gfycat'], $matches['token']));
		} else {
			$entry->_content(sprintf('%s <p>%s</p>', $content, $href));
		}

		return $entry;
	}
}
