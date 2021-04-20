<?php

namespace RedditImage\Transformer;

use RedditImage\Content;

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
            $dom = $this->generateImageDom('Image link', [$href]);
            $entry->_content("{$dom->saveHTML()}{$content->getRaw()}");
        } elseif (preg_match('#(?P<gifv>.*imgur.com/[^/]*.)gifv$#', $href, $matches)) {
            $videos = [
                [
                    'link' => $matches['gifv']. "mp4",
                    'format' => 'video/mp4',
                ]
            ];
            $dom = $this->generateVideoDom('Imgur gifv', $videos);
            $entry->_content("{$dom->saveHTML()}{$content->getRaw()}");
        } elseif (preg_match('#(?P<imgur>imgur.com/[^/]*)$#', $href)) {
            $dom = $this->generateImageDom('Imgur image with URL token', ["$href.png"]);
            $entry->_content("{$dom->saveHTML()}{$content->getRaw()}");
        } elseif (preg_match('#(?P<gfycat>gfycat.com/)(.*/)*(?P<token>[^/\-.]*)#', $href, $matches)) {
            try {
                $jsonResponse = file_get_contents("https://api.gfycat.com/v1/gfycats/{$matches['token']}");
                $arrayResponse = json_decode($jsonResponse, true);

                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new Exception();
                }

                $videos = [
                    [
                        'link' => $arrayResponse['gfyItem']['mp4Url'],
                        'format' => 'video/mp4',
                    ],
                    [
                        'link' => $arrayResponse['gfyItem']['webmUrl'],
                        'format' => 'video/webm',
                    ],
                ];
                $dom = $this->generateVideoDom('Gfycat with token', $videos);
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

                $videos = [
                    [
                        'link' => $arrayResponse['gfyItem']['mp4Url'],
                        'format' => 'video/mp4',
                    ],
                ];
                $dom = $this->generateVideoDom('Redgifs with token', $videos);
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
                $videos = [
                    [
                        'link' => str_replace('?source=fallback', '', $videoUrl),
                        'format' => 'video/mp4',
                    ]
                ];
                $dom = $this->generateVideoDom('Reddit video', $videos);
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
                    $links = [];
                    foreach ($pictures as $id => $metadata) {
                        list(,$extension) = explode('/', $metadata['m']);
                        $links[] = "https://i.redd.it/{$id}.{$extension}";
                    }
                    $dom = $this->generateImageDom('Reddit gallery', $links);
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

                    $links = [];
                    $json = json_decode($jsonString, true);
                    if (JSON_ERROR_NONE !== json_last_error()) {
                        throw new Exception();
                    }
                    foreach ($json['data'] as $image) {
                        $links[] = $image['link'];
                    }
                    $dom = $this->generateImageDom('Imgur gallery with API token', $links);
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
