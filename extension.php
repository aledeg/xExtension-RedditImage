<?php

use RedditImage\Transformer\DisplayTransformer;
use RedditImage\Transformer\InsertTransformer;

class RedditImageExtension extends Minz_Extension {
    const DEFAULT_HEIGHT = 70;
    const DEFAULT_MUTEDVIDEO = true;
    const DEFAULT_DISPLAYIMAGE = true;
    const DEFAULT_DISPLAYVIDEO = true;
    const DEFAULT_DISPLAYORIGINAL = true;

    private $displayTransformer;
    private $insertTransformer;

    public function autoload($class_name) {
        if (0 === strpos($class_name, 'RedditImage')) {
            $class_name = substr($class_name, 12);
            include $this->getPath() . DIRECTORY_SEPARATOR . str_replace('\\', '/', $class_name) . '.php';
        }
    }

    public function init() {
        spl_autoload_register(array($this, 'autoload'));

        $this->registerTranslates();

        $current_user = Minz_Session::param('currentUser');
        $filename = 'style.' . $current_user . '.css';
        $filepath = join_path($this->getPath(), 'static', $filename);

        if (file_exists($filepath)) {
            Minz_View::appendStyle($this->getFileUrl($filename, 'css'));
        }

        $this->displayTransformer = new DisplayTransformer($this->getDisplayImage(), $this->getDisplayVideo(), $this->getMutedVideo(), $this->getDisplayOriginal());
        $this->insertTransformer = new InsertTransformer();

        $this->registerHook('entry_before_display', array($this->displayTransformer, 'transform'));
        $this->registerHook('entry_before_insert', array($this->insertTransformer, 'transform'));
    }

    public function handleConfigureAction() {
        $this->registerTranslates();

        $current_user = Minz_Session::param('currentUser');

        if (Minz_Request::isPost()) {
            $configuration = [
                'imageHeight' => (int) Minz_Request::param('image-height', static::DEFAULT_HEIGHT),
                'mutedVideo' => (bool) Minz_Request::param('muted-video'),
                'displayImage' => (bool) Minz_Request::param('display-image'),
                'displayVideo' => (bool) Minz_Request::param('display-video'),
                'displayOriginal' => (bool) Minz_Request::param('display-original'),
            ];
            $this->setUserConfiguration($configuration);
            file_put_contents(join_path($this->getPath(), 'static', "style.{$current_user}.css"), sprintf(
                'img.reddit-image, video.reddit-image {max-height:%svh;}',
                $configuration['imageHeight']
            ));
        }
    }

    public function getImageHeight() {
        return $this->getUserConfigurationValue('imageHeight', static::DEFAULT_HEIGHT);
    }

    public function getMutedVideo() {
        return $this->getUserConfigurationValue('mutedVideo', static::DEFAULT_MUTEDVIDEO);
    }

    public function getDisplayImage() {
        return $this->getUserConfigurationValue('displayImage', static::DEFAULT_DISPLAYIMAGE);
    }

    public function getDisplayVideo() {
        return $this->getUserConfigurationValue('displayVideo', static::DEFAULT_DISPLAYVIDEO);
    }

    public function getDisplayOriginal() {
        return $this->getUserConfigurationValue('displayOriginal', static::DEFAULT_DISPLAYORIGINAL);
    }
}
