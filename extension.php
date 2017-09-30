<?php

class RedditImageExtension extends Minz_Extension {
	public function init() {
		$this->registerHook('entry_before_insert', array($this, 'transformLink'));
	}
	
	public function transformLink($entry) {
		$link = $entry->link();
		
		if (0 === strpos('reddit.com', $link)) {
			return $entry;
		}
		
		$content = $entry->content();

		// Change entry link by content link
		if (preg_match('/<a href="([^"]*)">\[link\]<\/a>/', $content, $matches)) {
			$href = $matches[1];
                        $entry->_link($href);
		}

		// Add image tag in content when the href links to an image
		if (preg_match('/(jpg|png|gif|bmp)$/', $href)) {
			$entry->_content(sprintf('%s <img src="%s" />', $content, $href));
		}
		
		return $entry;
	}
}
