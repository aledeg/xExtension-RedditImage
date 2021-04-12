<?php

namespace RedditImage\Transformer;

use RedditImage\Content;

class InsertTransformer extends AbstractTransformer {
    public function transform($entry) {

        if (false === $this->isRedditLink($entry)) {
            return $entry;
        }

        $content = new Content($entry->content());
        if (null === $href = $content->getContentLink()) {
            return $entry;
        }

        if (preg_match('#(?P<gfycat>gfycat.com/)(.*/)*(?P<token>[^/\-.]*)#', $href, $matches)) {
            try {
                $jsonResponse = file_get_contents("https://api.gfycat.com/v1/gfycats/{$matches['token']}");
                $arrayResponse = json_decode($jsonResponse, true);
                $videoUrl = $arrayResponse['gfyItem']['mp4Url'];
                if (!empty($videoUrl)) {
                    $entry->_content($this->getModifiedContentLink($entry, $videoUrl));
                }
            } catch (Exception $e) {
                Minz_Log::error("GFYCAT API ERROR - {$href}");
            }
        } elseif (preg_match('#(?P<redgifs>redgifs.com/)(.*/)*(?P<token>[^/\-.]*)#', $href, $matches)) {
            try {
                $jsonResponse = file_get_contents("https://api.redgifs.com/v1/gfycats/{$matches['token']}");
                $arrayResponse = json_decode($jsonResponse, true);
                $videoUrl = $arrayResponse['gfyItem']['mp4Url'];
                if (!empty($videoUrl)) {
                    $entry->_content($this->getModifiedContentLink($entry, $videoUrl));
                }
            } catch (Exception $e) {
                Minz_Log::error("REDGIFS API ERROR - {$href}");
            }
        } elseif (preg_match('#v.redd.it#', $href)) {
            try {
                $jsonResponse = file_get_contents("{$content->getCommentsLink()}.json");
                $arrayResponse = json_decode($jsonResponse, true);
                $videoUrl = $arrayResponse[0]['data']['children'][0]['data']['media']['reddit_video']['fallback_url'];
                if (!empty($videoUrl)) {
                    $videoUrl = str_replace('?source=fallback', '', $videoUrl);
                    $entry->_content($this->getModifiedContentLink($entry, $videoUrl));
                }
            } catch (Exception $e) {
                Minz_Log::error("REDDIT API ERROR - {$href}");
            }
        } elseif (preg_match('#reddit.com/gallery#', $href)) {
            try {
                $jsonResponse = file_get_contents("{$content->getCommentsLink()}.json");
                $arrayResponse = json_decode($jsonResponse, true);
                $pictures = $arrayResponse[0]['data']['children'][0]['data']['media_metadata'];
                if (!empty($pictures)) {
                    $dom = new \DomDocument();

                    $div = $dom->appendChild($dom->createElement('div'));
                    $div->setAttribute('class', 'reddit-image figure');

                    $div->appendChild($dom->createComment('xExtension-RedditImage | InsertTransformer | Reddit gallery'));

                    foreach ($pictures as $id => $metadata) {
                        list(,$extension) = explode('/', $metadata['m']);
                        $img = $div->appendChild($dom->createElement('img'));
                        $img->setAttribute('src', "https://i.redd.it/{$id}.{$extension}");
                        $img->setAttribute('class', 'reddit-image');
                    }
                    $entry->_content("{$dom->saveHTML()}{$content->getRaw()}");
                }
            } catch (Exception $e) {
                Minz_Log::error("REDDIT API ERROR - {$href}");
            }
        }

        return $entry;
    }

    private function getModifiedContentLink($entry, $link) {
        return preg_replace('#<a href="(?P<href>[^"]*)">\[link\]</a>#', "<a href=\"${link}\">[link]</a>", $entry->content());
    }
}
