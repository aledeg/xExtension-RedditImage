<?php

namespace RedditImage\Transformer;

use RedditImage\Content;
use RedditImage\Media\Image;
use RedditImage\Media\Video;

class InsertTransformer extends AbstractTransformer {
    private $imgurClientId;

    public function __construct(string $imgurClientId = null) {
        $this->imgurClientId = $imgurClientId;
    }

    public function transform($entry) {

        if (false === $this->isRedditLink($entry)) {
            return $entry;
        }

        $content = new Content($entry->content());
        if (null === $href = $content->getContentLink()) {
            return $entry;
        }

        if (preg_match('#(jpg|png|gif|bmp)(\?.*)?$#', $href)) {
            $dom = $this->generateImageDom('Image link', [new Image($href)]);
            $entry->_content("{$dom->saveHTML()}{$content->getRaw()}");
        } elseif (preg_match('#(?P<gifv>.*imgur.com/[^/]*.)gifv$#', $href, $matches)) {
            $dom = $this->generateVideoDom('Imgur gifv', [new Video('video/mp4', $matches['gifv']. "mp4")]);
            $entry->_content("{$dom->saveHTML()}{$content->getRaw()}");
        } elseif (preg_match('#(?P<imgur>imgur.com/[^/]*)$#', $href)) {
            $dom = $this->generateImageDom('Imgur image with URL token', [new Image("$href.png")]);
            $entry->_content("{$dom->saveHTML()}{$content->getRaw()}");
        } elseif (preg_match('#(?P<gfycat>gfycat.com/)(.*/)*(?P<token>[^/\-.]*)#', $href, $matches)) {
            try {
                $jsonResponse = file_get_contents("https://api.gfycat.com/v1/gfycats/{$matches['token']}");
                $arrayResponse = json_decode($jsonResponse, true);

                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new Exception();
                }

                $video = new Video('video/mp4', $arrayResponse['gfyItem']['mp4Url']);
                $video->addSource('video/webm', $arrayResponse['gfyItem']['webmUrl']);
                $dom = $this->generateVideoDom('Gfycat with token', [$video]);
                $entry->_content("{$dom->saveHTML()}{$content->getRaw()}");
            } catch (Exception $e) {
                Minz_Log::error("GFYCAT API ERROR - {$href}");
            }
        } elseif (preg_match('#(?P<redgifs>redgifs.com/)(.*/)*(?P<token>[^/\-.]*)#', $href, $matches)) {
            try {
                $jsonResponse = file_get_contents("https://api.redgifs.com/v1/gfycats/{$matches['token']}");
                $arrayResponse = json_decode($jsonResponse, true);

                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new Exception();
                }

                $dom = $this->generateVideoDom('Redgifs with token', [new Video('video/mp4', $arrayResponse['gfyItem']['mp4Url'])]);
                $entry->_content("{$dom->saveHTML()}{$content->getRaw()}");
            } catch (Exception $e) {
                Minz_Log::error("REDGIFS API ERROR - {$href}");
            }
        } elseif (preg_match('#v.redd.it#', $href)) {
            try {
                $jsonResponse = file_get_contents("{$content->getCommentsLink()}.json");
                $arrayResponse = json_decode($jsonResponse, true);

                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new Exception();
                }

                $videoUrl = $arrayResponse[0]['data']['children'][0]['data']['media']['reddit_video']['fallback_url'];
                $dom = $this->generateVideoDom('Reddit video', [new Video('video/mp4', str_replace('?source=fallback', '', $videoUrl))]);
                $entry->_content("{$dom->saveHTML()}{$content->getRaw()}");
            } catch (Exception $e) {
                Minz_Log::error("REDDIT API ERROR - {$href}");
            }
        } elseif (preg_match('#reddit.com/gallery#', $href)) {
            try {
                $jsonResponse = file_get_contents("{$content->getCommentsLink()}.json");
                $arrayResponse = json_decode($jsonResponse, true);
                $pictures = $arrayResponse[0]['data']['children'][0]['data']['media_metadata'];
                if (!empty($pictures)) {
                    $images = [];
                    foreach ($pictures as $id => $metadata) {
                        list(,$extension) = explode('/', $metadata['m']);
                        $images[] = new Image("https://i.redd.it/{$id}.{$extension}");
                    }
                    $dom = $this->generateImageDom('Reddit gallery', $images);
                    $entry->_content("{$dom->saveHTML()}{$content->getRaw()}");
                }
            } catch (Exception $e) {
                Minz_Log::error("REDDIT API ERROR - {$href}");
            }
        } elseif (preg_match('#imgur.com/(a|gallery)/.?#', $href)) {
            try {
                if (0 < strlen($this->imgurClientId)) {
                    $token = basename($href);
                    $ch = curl_init(); 
                    curl_setopt($ch, CURLOPT_URL, "https://api.imgur.com/3/album/$token/images");
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Client-ID {$this->imgurClientId}"]);
                    curl_setopt($ch, CURLOPT_FAILONERROR, true);
                    $jsonString = curl_exec($ch);
                    if (curl_errno($ch)) {
                        throw new Exception();
                    }
                    curl_close($ch);

                    $images = [];
                    $json = json_decode($jsonString, true);
                    if (JSON_ERROR_NONE !== json_last_error()) {
                        throw new Exception();
                    }
                    foreach ($json['data'] as $image) {
                        $images[] = new Image($image['link']);
                    }
                    $dom = $this->generateImageDom('Imgur gallery with API token', $images);
                    $entry->_content("{$dom->saveHTML()}{$content->getRaw()}");
                } else {
                    $galleryDom = new \DomDocument();
                    $galleryDom->loadHTML(file_get_contents($href), LIBXML_NOERROR);
                    $galleryXpath = new \DomXpath($galleryDom);
                    $images = $galleryXpath->query("//meta[@name='twitter:image']");
                    foreach ($images as $image) {
                        $links[] = $image->getAttribute('content');
                    }
                    $dom = $this->generateImageDom('Imgur gallery without API token', $links);
                    $entry->_content("{$dom->saveHTML()}{$content->getRaw()}");
                }
            } catch (Exception $e) {
                Minz_Log::error("IMGUR GALLERY ERROR - {$href}");
            }
        }

        return $entry;
    }
}
