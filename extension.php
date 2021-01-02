<?php

class RedditImageExtension extends Minz_Extension {
    const IMAGE_CONTENT = '<div class="reddit-image figure"><img src="%s" class="reddit-image"/></div>';
    const VIDEO_CONTENT = '<div class="reddit-image figure"><video controls preload="metadata" %4$s class="reddit-image"><source src="%1$s" type="video/webm"><source src="%2$s" type="video/mp4"></video></div>';
    const LINK_CONTENT = '<p><a href="%1$s">%1$s</a></p>';
    const GFYCAT_API = 'https://api.gfycat.com/v1/gfycats/%s';
    const REDGIFS_API = 'https://api.redgifs.com/v1/gfycats/%s';
    const MATCH_REDDIT = 'reddit.com';

    const DEFAULT_HEIGHT = 70;
    const DEFAULT_MUTEDVIDEO = true;
    const DEFAULT_DISPLAYIMAGE = true;
    const DEFAULT_DISPLAYVIDEO = true;
    const DEFAULT_DISPLAYORIGINAL = true;

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
        $this->registerHook('entry_before_insert', array($this, 'updateRedgifsLink'));
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
                'displayOriginal' => (bool) Minz_Request::param('display-original'),
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

        if (null === $href = $this->extractOriginalContentLink($entry)) {
            return $entry;
        }

        $this->getConfiguration();

        $content = '';

        // Add image tag in content when the href links to an image
        if (preg_match('#(jpg|png|gif|bmp)(\?.*)?$#', $href)) {
            $content = $this->getNewImageContent($href);
        // Add image tag in content when the href links to an imgur gifv
        } elseif (preg_match('#(?P<gifv>.*imgur.com/[^/]*).gifv$#', $href, $matches)) {
            $href = "${matches['gifv']}.gif";
            $content = $this->getNewImageContent($href);
        // Add image tag in content when the href links to an imgur image
        } elseif (preg_match('#(?P<imgur>imgur.com/[^/]*)$#', $href)) {
            $href = "${href}.png";
            $content = $this->getNewImageContent($href);
        // Add video tag in content when the href links to a video
        } elseif (preg_match('#(?P<baseurl>.+)(webm|mp4)$#', $href, $matches)) {
            $content = $this->getNewVideoContent($entry, $matches['baseurl']);
        } else {
            $content = $this->getNewLinkContent($href);
        }

        if ($this->display_original) {
            $content .= $entry->content();
        }
        $entry->_content($content);
        $entry->_link($href);

        return $entry;
    }

    public function updateLink($entry, $pattern, $apiUrl) {
        if (false === $this->isRedditLink($entry)) {
            return $entry;
        }

        if (null === $href = $this->extractOriginalContentLink($entry)) {
            return $entry;
        }

        $this->getConfiguration();

        if (preg_match($pattern, $href, $matches)) {
            try {
                $jsonResponse = file_get_contents(sprintf($apiUrl, $matches['token']));
                $arrayResponse = json_decode($jsonResponse, true);
                $videoUrl = $arrayResponse['gfyItem']['mp4Url'];
                if (empty($videoUrl)) {
                    return $entry;
                }
                $newContent = preg_replace('#<a href="(?P<href>[^"]*)">\[link\]</a>#', "<a href=\"${videoUrl}\">[link]</a>", $entry->content());
            } catch (Exception $e) {
                $newContent = sprintf('%s <p>API ERROR</p>', $entry->content());
            }
            $entry->_content($newContent);
        }

        return $entry;
    }

    public function updateGfycatLink($entry) {
        return $this->updateLink($entry, '#(?P<gfycat>gfycat.com/)(.*/)*(?P<token>[^/\-.]*)#', static::GFYCAT_API);
    }

    public function updateRedgifsLink($entry) {
        return $this->updateLink($entry, '#(?P<redgifs>redgifs.com/)(.*/)*(?P<token>[^/\-.]*)#', static::REDGIFS_API);
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
    private function extractOriginalContentLink($entry) {
        if (preg_match('#<a href="(?P<href>[^"]*)">\[link\]</a>#', $entry->content(), $matches)) {
            return $matches['href'];
        }
    }

    private function getNewImageContent($href) {
        if (!$this->display_image) {
            return;
        }
        return sprintf(static::IMAGE_CONTENT,$href);
    }

    private function getNewVideoContent($entry, $baseUrl) {
        if (!$this->display_video) {
            return;
        }
        $hrefMp4 = $baseUrl . 'mp4';
        $hrefWebm = $baseUrl . 'webm';
        return sprintf(static::VIDEO_CONTENT, $hrefWebm, $hrefMp4, $entry->link(), $this->muted_video ? 'muted': '');
    }

    private function getNewLinkContent($href) {
        return sprintf(static::LINK_CONTENT, $href);
    }

    private function getConfiguration() {
        $current_user = Minz_Session::param('currentUser');
        $filename = 'configuration.' . $current_user . '.json';
        $filepath = join_path($this->getPath(), 'static', $filename);

        $this->image_height = static::DEFAULT_HEIGHT;
        $this->muted_video = static::DEFAULT_MUTEDVIDEO;
        $this->display_image = static::DEFAULT_DISPLAYIMAGE;
        $this->display_video = static::DEFAULT_DISPLAYVIDEO;
        $this->display_original = static::DEFAULT_DISPLAYORIGINAL;
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
            if (array_key_exists('displayOriginal', $configuration)) {
                $this->display_original = $configuration['displayOriginal'];
            }
        }
    }
}
