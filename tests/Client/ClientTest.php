<?php

declare(strict_types=1);

namespace RedditImage\Tests\Client;

use RedditImage\Client\Client;
use RedditImage\Tests\PHPUnit\TestCase;

/**
 * @covers Client
 *
 * Validates that the external API calls are still returning the same structure.
 * As those tests are really making calls to the live API, they are separated in
 * the configuration to limit the number of calls. For that same reason, those
 * tests might break for reasons outside the scope of that project.
 */
class ClientTest extends TestCase
{
    private Client $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client('Reddit Image/test by Alexis Degrugillier');
    }

    /**
     * @testWith ["https://example.org", true]
     *           ["https://github.com/aledeg/xExtension-RedditImage/404", false]
     */
    public function testIsAccessible(string $url, bool $isAccessible): void
    {
        $this->assertEquals($isAccessible, $this->client->isAccessible($url));
    }

    public function testJsonGetFlickrImage(): void
    {
        if (!defined('FLICKR_API_KEY')) {
            $this->markTestSkipped('Flickr api key is not defined in the configuration file');
        }

        $apiKey = FLICKR_API_KEY;
        $json = $this->client->jsonGet(
            "https://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key={$apiKey}&photo_id=51962178667&format=json",
            [],
            static function (string $payload) {
                return preg_replace(['/^jsonFlickrApi\(/', '/\)$/'], '', $payload);
            }
        );
        $this->assertIsArray($json);

        $metadata = $json['sizes']['size'] ?? [];
        $this->assertCount(15, $metadata);

        $largestImage = array_pop($metadata);
        $this->assertEquals('https://live.staticflickr.com/65535/51962178667_fc163483f7_o.jpg', $largestImage['source']);
    }

    public function testJsonGetImgurGalleryWithClientId(): void
    {
        if (!defined('IMGUR_CLIENT_ID')) {
            $this->markTestSkipped('Imgur client ID is not defined in the configuration file');
        }

        $clientId = IMGUR_CLIENT_ID;
        $json = $this->client->jsonGet('https://api.imgur.com/3/album/JOh3bhn/images', [
            "Authorization: Client-ID {$clientId}",
        ]);
        $this->assertIsArray($json);

        $metadata = $json['data'];
        $this->assertIsArray($metadata);
        $this->assertCount(14, $metadata);

        $links = array_column($metadata, 'link');
        $this->assertIsArray($links);
        $this->assertCount(14, $links);
        $this->assertEquals([
            'https://i.imgur.com/kHJbvmh.png',
            'https://i.imgur.com/M5XwH7K.png',
            'https://i.imgur.com/nIAIjJO.png',
            'https://i.imgur.com/ASyDzpc.png',
            'https://i.imgur.com/aMQ00r6.png',
            'https://i.imgur.com/F6mnogZ.png',
            'https://i.imgur.com/hGgJ4vX.png',
            'https://i.imgur.com/tnd6dKW.png',
            'https://i.imgur.com/Bi1mSnq.png',
            'https://i.imgur.com/4mIdYn5.png',
            'https://i.imgur.com/HcburKp.png',
            'https://i.imgur.com/dtiq3CN.png',
            'https://i.imgur.com/07byVN9.png',
            'https://i.imgur.com/U07O5Co.png',
        ], $links);
    }

    public function testJsonGetRedditGallery(): void
    {
        $json = $this->client->jsonGet('https://www.reddit.com/r/quilting/comments/13ajhjf/lochness_monster_fpp_block/.json');
        $this->assertIsArray($json);

        $metadata = $json[0]['data']['children'][0]['data']['media_metadata'];
        $this->assertIsArray($metadata);

        $this->assertCount(7, $metadata);
        $this->assertEquals([
            'm8do0y12wdya1',
            'fvbthx12wdya1',
            'nb0y7y12wdya1',
            '2bel5y12wdya1',
            'chnqmy12wdya1',
            '9w7sby12wdya1',
            'rgww3y12wdya1',
        ], array_keys($metadata));
        foreach ($metadata as $values) {
            $this->assertArrayHasKey('m', $values);
            $this->assertEquals('image/jpg', $values['m']);
        }
    }

    public function testJsonGetRedditGalleryWithCrosspost(): void
    {
        $json = $this->client->jsonGet('https://www.reddit.com/r/LegendsOfRuneterra/comments/l2k33y/might_be_an_obvious_connection_but_i_just_noticed/.json');
        $this->assertIsArray($json);

        $metadata = $json[0]['data']['children'][0]['data']['crosspost_parent_list'][0]['media_metadata'];
        $this->assertCount(4, $metadata);
        $this->assertEquals([
            'xpt2x73houc61',
            '5lztrcniouc61',
            'fnwszdajouc61',
            '9wq3m82iouc61',
        ], array_keys($metadata));
        foreach ($metadata as $values) {
            $this->assertArrayHasKey('m', $values);
            $this->assertEquals('image/jpg', $values['m']);
        }
    }

    public function testJsonGetRedditVideo(): void
    {
        $json = $this->client->jsonGet('https://www.reddit.com/r/oddlyterrifying/comments/13ommr2/while_some_find_a_baby_stingray_video_shoot/.json');
        $this->assertIsArray($json);

        $metadata = $json[0]['data']['children'][0]['data']['media']['reddit_video']['fallback_url'] ?? null;
        $this->assertEquals('https://v.redd.it/nvcnk4qsee1b1/DASH_1080.mp4?source=fallback', $metadata);
    }

    public function testJsonGetRedditVideoWithCrosspost(): void
    {
        $json = $this->client->jsonGet('https://www.reddit.com/r/hiddenrooms/comments/11vohr2/crosspost_from_rcarpentry_throwback_to_a_really/.json');
        $this->assertIsArray($json);

        $metadata = $json[0]['data']['children'][0]['data']['crosspost_parent_list'][0]['media']['reddit_video']['fallback_url'] ?? null;
        $this->assertEquals('https://v.redd.it/a9ticvrffpoa1/DASH_1080.mp4?source=fallback', $metadata);
    }
}
