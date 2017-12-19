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
			$entry->_content(sprintf('%s <img src="%s" class="reddit-image" alt="IMAGE URL" />', $content, $href));
		// Add image tag in content when the href links to an imgur gifv
		} elseif (preg_match('#(?P<gifv>.*imgur.com/[^/]*).gifv$#', $href, $matches)) {
			$entry->_content(sprintf('%s <img src="%s.gif" class="reddit-image" alt="IMGUR GIFV" />', $content, $matches['gifv']));
		// Add image tag in content when the href links to an imgur image
		} elseif (preg_match('#(?P<imgur>imgur.com/[^/]*)$#', $href)) {
			$entry->_content(sprintf('%s <img src="%s.png" class="reddit-image" alt="IMGUR TOKEN" />', $content, $href));
		// Add video tag in content when the href links to a video
		} elseif (preg_match('#(?P<extension>webm|mp4)$#', $href, $matches)) {
			$entry->_content(sprintf('%s  <video controls><source src="%s" type="video/%s">VIDEO URL</video>', $content, $href, $matches['extension']));
		// Add video tag in content when the href links to a gfycat video
		} elseif (preg_match('#(?P<gfycat>gfycat.com/)(.*/)*(?P<token>[^/.]*)$#', $href, $matches)) {
			$entry->_content(sprintf('%s <video controls><source src="https://giant.%s%s.mp4" type="video/mp4">GFYCAT TOKEN</video>', $content, $matches['gfycat'], $matches['token']));
		} else {
			$entry->_content(sprintf('%s <p>%s</p>', $content, $href));
		}

		return $entry;
	}
}
