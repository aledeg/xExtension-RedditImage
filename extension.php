<?php

class RedditImageExtension extends Minz_Extension {
    const IMAGE_CONTENT = '<div class="reddit-image figure"><a href="%1$s"><img src="%1$s" class="reddit-image"/></a><p class="caption"><a href="%2$s">Comments</a></p></div>';
    const VIDEO_CONTENT = '<div class="reddit-image figure"><video controls preload="metadata" %4$s class="reddit-image"><source src="%1$s" type="video/webm"><source src="%2$s" type="video/mp4"></video><p class="caption"><a href="%3$s">Comments</a></p></div>';
    const LINK_CONTENT = '%1$s<p><a href="%2$s">%2$s</a></p>';
    const GFYCAT_API = 'https://api.gfycat.com/v1/gfycats/%s';
    const MATCH_REDDIT = 'reddit.com';

    const DEFAULT_HEIGHT = 70;
    const DEFAULT_MUTEDVIDEO = true;
    const DEFAULT_DISPLAYIMAGE = true;
    const DEFAULT_DISPLAYVIDEO = true;

    public function init() {
        $this->registerTranslates();

        $current_user = Minz_Session::param('currentUser');
        $filename = 'style.' . $current_user . '.css';
        $filepath = join_path($this->getPath(), 'static', $filename);

        if (file_exists($filepath)) {
            Minz_View::appendStyle($this->getFileUrl($filename, 'css'));
        }

        $this->registerHook('entry_before_display', array($this, 'transformEntry'));
        $this->registerHook('entry_before_insert', array($this, 'updateGfycatLink'));
    }

    public function handleConfigureAction() {
        $this->registerTranslates();

        $current_user = Minz_Session::param('currentUser');
        $filename = 'configuration.' . $current_user . '.json';
        $filepath = join_path($this->getPath(), 'static', $filename);

        if (Minz_Request::isPost()) {
            $configuration = array(
                'imageHeight' => (int) Minz_Request::param('image-height', static::DEFAULT_HEIGHT),
                'mutedVideo' => (bool) Minz_Request::param('muted-video'),
                'displayImage' => (bool) Minz_Request::param('display-image'),
                'displayVideo' => (bool) Minz_Request::param('display-video'),
            );
            file_put_contents($filepath, json_encode($configuration));
            file_put_contents(join_path($this->getPath(), 'static', "style.{$current_user}.css"), sprintf(
                'img.reddit-image, video.reddit-image {max-height:%svh;}',
                $configuration['imageHeight']
            ));
        }

        $this->getConfiguration();
    }

    public function transformEntry($entry) {
        if (false === $this->isRedditLink($entry)) {
            return $entry;
        }

        if (null === $href = $this->getContentLink($entry)) {
            return $entry;
        }

        $this->getConfiguration();

        // Add image tag in content when the href links to an image
        if (preg_match('#(jpg|png|gif|bmp)(\?.*)?$#', $href)) {
            $this->addImageContent($entry, $href);
        // Add image tag in content when the href links to an imgur gifv
        } elseif (preg_match('#(?P<gifv>.*imgur.com/[^/]*).gifv$#', $href, $matches)) {
            $href = "${matches['gifv']}.gif";
            $this->addImageContent($entry, $href);
        // Add image tag in content when the href links to an imgur image
        } elseif (preg_match('#(?P<imgur>imgur.com/[^/]*)$#', $href)) {
            $href = "${href}.png";
            $this->addImageContent($entry, $href);
        // Add video tag in content when the href links to a video
        } elseif (preg_match('#(?P<baseurl>.+)(webm|mp4)$#', $href, $matches)) {
            $this->addVideoContent($entry, $matches['baseurl']);
        } else {
            $this->addLinkContent($entry, $href);
        }

        $entry->_link($href);

        return $entry;
    }

    public function updateGfycatLink($entry) {
        if (false === $this->isRedditLink($entry)) {
            return $entry;
        }

        if (null === $href = $this->getContentLink($entry)) {
            return $entry;
        }

        $this->getConfiguration();

        if (preg_match('#(?P<gfycat>gfycat.com/)(.*/)*(?P<token>[^/\-.]*)#', $href, $matches)) {
            try {
                $jsonResponse = file_get_contents(sprintf(static::GFYCAT_API, $matches['token']));
                $arrayResponse = json_decode($jsonResponse, true);
                $videoUrl = $arrayResponse['gfyItem']['mp4Url'];
                $newContent = preg_replace('#<a href="(?P<href>[^"]*)">\[link\]</a>#', "<a href=\"${videoUrl}\">[link]</a>", $entry->content());
            } catch (Exception $e) {
                $newContent = sprintf('%s <p>GFYCAT ERROR</p>', $entry->content());
            }
            $entry->_content($newContent);
        }

        return $entry;
    }

    /**
     * @return bool
     */
    private function isRedditLink($entry) {
        return (bool) strpos($entry->link(), static::MATCH_REDDIT);
    }

    /**
     * @return string|null
     */
    private function getContentLink($entry) {
        if (preg_match('#<a href="(?P<href>[^"]*)">\[link\]</a>#', $entry->content(), $matches)) {
            return $matches['href'];
        }
    }

    private function addImageContent($entry, $href) {
        if (!$this->display_image) {
            return;
        }
        $entry->_content(sprintf(static::IMAGE_CONTENT,$href, $entry->link()));
    }

    private function addVideoContent($entry, $baseUrl) {
        if (!$this->display_video) {
            return;
        }
        $hrefMp4 = $baseUrl . 'mp4';
        $hrefWebm = $baseUrl . 'webm';
        $entry->_content(sprintf(static::VIDEO_CONTENT, $hrefWebm, $hrefMp4, $entry->link(), $this->muted_video ? 'muted': ''));
    }

    private function addLinkContent($entry, $href) {
        $entry->_content(sprintf(static::LINK_CONTENT, $entry->content(), $href));
    }

    private function getConfiguration() {
        $current_user = Minz_Session::param('currentUser');
        $filename = 'configuration.' . $current_user . '.json';
        $filepath = join_path($this->getPath(), 'static', $filename);

        $this->image_height = static::DEFAULT_HEIGHT;
        $this->muted_video = static::DEFAULT_MUTEDVIDEO;
        $this->display_image = static::DEFAULT_DISPLAYIMAGE;
        $this->display_video = static::DEFAULT_DISPLAYVIDEO;
        if (file_exists($filepath)) {
            $configuration = json_decode(file_get_contents($filepath), true);
            if (array_key_exists('imageHeight', $configuration)) {
                $this->image_height = $configuration['imageHeight'];
            }
            if (array_key_exists('mutedVideo', $configuration)) {
                $this->muted_video = $configuration['mutedVideo'];
            }
            if (array_key_exists('displayImage', $configuration)) {
                $this->display_image = $configuration['displayImage'];
            }
            if (array_key_exists('displayVideo', $configuration)) {
                $this->display_video = $configuration['displayVideo'];
            }
        }
    }
}
