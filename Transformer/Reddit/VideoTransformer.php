<?php

declare(strict_types=1);

namespace RedditImage\Transformer\Reddit;

use RedditImage\Content;
use RedditImage\Media\Video;
use RedditImage\Transformer\AbstractTransformer;
use RedditImage\Transformer\TransformerInterface;

class VideoTransformer extends AbstractTransformer implements TransformerInterface
{
    public function canTransform(Content $content): bool
    {
        return preg_match('#v.redd.it#', $content->getContentLink()) === 1;
    }

    public function transform(Content $content): string
    {
        if ('' === $videoUrl = $this->getVideoUrl($content)) {
            return '';
        }

        $dom = $this->generateDom([new Video('video/mp4', $videoUrl, $this->getAudioUrl($videoUrl))]);
        sleep($this->settings->getRedditDelay());

        return $dom->saveHTML() ?: '';
    }

    private function getVideoUrl(Content $content): string
    {
        $arrayResponse = $this->client->jsonGet("{$content->getCommentsLink()}.json");

        $videoUrl = $arrayResponse[0]['data']['children'][0]['data']['media']['reddit_video']['fallback_url']
            ?? $arrayResponse[0]['data']['children'][0]['data']['crosspost_parent_list'][0]['media']['reddit_video']['fallback_url']
            ?? '';

        return str_replace('?source=fallback', '', $videoUrl);
    }

    private function getAudioUrl(string $videoUrl): string
    {
        return preg_replace('#DASH_.+\.mp4#', 'DASH_audio.mp4', $videoUrl);
    }
}
