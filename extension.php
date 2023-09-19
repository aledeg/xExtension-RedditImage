<?php

declare(strict_types=1);

use RedditImage\Client\Client;
use RedditImage\Processor\BeforeInsertProcessor;
use RedditImage\Processor\BeforeDisplayProcessor;
use RedditImage\Settings;

class RedditImageExtension extends Minz_Extension
{
    private BeforeDisplayProcessor $beforeDisplayProcessor;
    private BeforeInsertProcessor $beforeInsertProcessor;
    public Settings $settings;

    public function autoload(string $class_name): void
    {
        if (0 === strpos($class_name, 'RedditImage')) {
            $class_name = substr($class_name, 12);
            include $this->getPath() . DIRECTORY_SEPARATOR . str_replace('\\', '/', $class_name) . '.php';
        }
    }

    private function getUserAgent(): string
    {
        return "{$this->getName()}/{$this->getVersion()} by {$this->getAuthor()}";
    }

    public function init(): void
    {
        spl_autoload_register([$this, 'autoload']);

        define('REDDITIMAGE_VERSION', $this->getVersion());
        $this->registerTranslates();

        $current_user = Minz_Session::param('currentUser');
        $filename = "style.{$current_user}.css";
        $filepath = join_path($this->getPath(), 'static', $filename);

        if (file_exists($filepath)) {
            Minz_View::appendStyle($this->getFileUrl($filename, 'css'));
        }

        $this->settings = new Settings($this->getUserConfiguration());

        $this->beforeDisplayProcessor = new BeforeDisplayProcessor($this->settings);
        $this->beforeInsertProcessor = new BeforeInsertProcessor($this->settings, new Client($this->getUserAgent()));

        $this->registerHook('entry_before_display', [$this->beforeDisplayProcessor, 'process']);
        $this->registerHook('entry_before_insert', [$this->beforeInsertProcessor, 'process']);
    }

    public function handleConfigureAction(): void
    {
        $this->registerTranslates();

        $current_user = Minz_Session::param('currentUser');

        if (Minz_Request::isPost()) {
            $configuration = [
                'imageHeight' => (int) Minz_Request::param('image-height', $this->settings->getDefaultImageHeight()),
                'mutedVideo' => (bool) Minz_Request::param('muted-video'),
                'displayImage' => (bool) Minz_Request::param('display-image'),
                'displayVideo' => (bool) Minz_Request::param('display-video'),
                'displayOriginal' => (bool) Minz_Request::param('display-original'),
                'displayMetadata' => (bool) Minz_Request::param('display-metadata'),
                'displayThumbnails' => (bool) Minz_Request::param('display-thumbnails'),
                'imgurClientId' => Minz_Request::param('imgur-client-id'),
                'flickrApiKey' => Minz_Request::param('flickr-api-key'),
                'redditDelay' => (int) Minz_Request::param('reddit-delay'),
            ];
            $this->setUserConfiguration($configuration);
            file_put_contents(
                join_path($this->getPath(), 'static', "style.{$current_user}.css"),
                "img.reddit-image, video.reddit-image {max-height:{$configuration['imageHeight']}vh;}",
            );
        }
    }
}
