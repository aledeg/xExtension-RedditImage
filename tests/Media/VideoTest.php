<?php

declare(strict_types=1);

namespace RedditImage\Tests\Media;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use RedditImage\Media\DomElementInterface;
use RedditImage\Media\Video;

/**
 * @covers Video
 */
final class VideoTest extends TestCase
{
    private \DomDocument&m\MockInterface $dom;
    private \DomElement&m\MockInterface $video;

    public function setUp(): void
    {
        parent::setUp();

        $this->video = m::mock(\DomElement::class);
        $this->video->expects('setAttribute')
            ->once()
            ->with('controls', 'true');
        $this->video->expects('setAttribute')
            ->once()
            ->with('preload', 'metadata');
        $this->video->expects('setAttribute')
            ->once()
            ->with('class', 'reddit-image');

        $this->dom = m::mock(\DomDocument::class);
        $this->dom->expects('createElement')
            ->once()
            ->with('video')
            ->andReturns($this->video);
    }

    public function testWithoutData(): void
    {
        $media = new Video();

        $this->assertEquals($this->video, $media->toDomElement($this->dom));
    }

    public function testWithIncompleteVideoSource(): void
    {
        // No video URL
        $media = new Video('video/webm');
        $this->assertEquals($this->video, $media->toDomElement($this->dom));

        // No video type
        $media = new Video(null, 'https://example.org');
        $this->assertEquals($this->video, $media->toDomElement($this->dom));
    }

    public function testWithSingleVideoSourceAndNoAudioTrack(): void
    {
        $source = m::mock(\DomElement::class);
        $source->expects('setAttribute')
            ->once()
            ->with('src', 'https://example.org/video.webm');
        $source->expects('setAttribute')
            ->once()
            ->with('type', 'video/webm');

        $this->dom->expects('createElement')
            ->once()
            ->with('source')
            ->andReturns($source);

        $this->video->expects('appendChild')
            ->once()
            ->with($source)
            ->andReturns($source);

        $media = new Video('video/webm', 'https://example.org/video.webm');
        $this->assertEquals($this->video, $media->toDomElement($this->dom));
    }

    public function testWithMultipleVideoSourcesAndNoAudioTrack(): void
    {
        $source1 = m::mock(\DomElement::class);
        $source1->expects('setAttribute')
            ->once()
            ->with('src', 'https://example.org/video.webm');
        $source1->expects('setAttribute')
            ->once()
            ->with('type', 'video/webm');

        $this->dom->expects('createElement')
            ->once()
            ->with('source')
            ->andReturns($source1);

        $this->video->expects('appendChild')
            ->once()
            ->with($source1)
            ->andReturns($source1);

        $source2 = m::mock(\DomElement::class);
        $source2->expects('setAttribute')
            ->once()
            ->with('src', 'https://example.org/video.mp4');
        $source2->expects('setAttribute')
            ->once()
            ->with('type', 'video/mp4');

        $this->dom->expects('createElement')
            ->once()
            ->with('source')
            ->andReturns($source2);

        $this->video->expects('appendChild')
            ->once()
            ->with($source2)
            ->andReturns($source2);

        $media = new Video('video/webm', 'https://example.org/video.webm');
        $media->addSource('video/mp4', 'https://example.org/video.mp4');
        $this->assertEquals($this->video, $media->toDomElement($this->dom));
    }

    public function testWithSingleVideoSourceAndAudioTrack(): void
    {
        $audio = m::mock(\DomElement::class);
        $audio->expects('setAttribute')
            ->once()
            ->with('controls', 'true');

        $this->dom->expects('createElement')
            ->once()
            ->with('audio')
            ->andReturns($audio);

        $this->video->expects('appendChild')
            ->once()
            ->with($audio)
            ->andReturns($audio);

        $audioSource = m::mock(\DomElement::class);
        $audioSource->expects('setAttribute')
            ->once()
            ->with('src', 'https://example.org/audio.mp3');

        $this->dom->expects('createElement')
            ->once()
            ->with('source')
            ->andReturns($audioSource);

        $audio->expects('appendChild')
            ->once()
            ->with($audioSource)
            ->andReturns($audioSource);

        $videoSource = m::mock(\DomElement::class);
        $videoSource->expects('setAttribute')
            ->once()
            ->with('src', 'https://example.org/video.webm');
        $videoSource->expects('setAttribute')
            ->once()
            ->with('type', 'video/webm');

        $this->dom->expects('createElement')
            ->once()
            ->with('source')
            ->andReturns($videoSource);

        $this->video->expects('appendChild')
            ->once()
            ->with($videoSource)
            ->andReturns($videoSource);

        $media = new Video('video/webm', 'https://example.org/video.webm', 'https://example.org/audio.mp3');
        $this->assertEquals($this->video, $media->toDomElement($this->dom));
    }

    public function testWithMultipleVideoSourcesAndAudioTrack(): void
    {
        $audio = m::mock(\DomElement::class);
        $audio->expects('setAttribute')
            ->once()
            ->with('controls', 'true');

        $this->dom->expects('createElement')
            ->once()
            ->with('audio')
            ->andReturns($audio);

        $this->video->expects('appendChild')
            ->once()
            ->with($audio)
            ->andReturns($audio);

        $audioSource = m::mock(\DomElement::class);
        $audioSource->expects('setAttribute')
            ->once()
            ->with('src', 'https://example.org/audio.mp3');

        $this->dom->expects('createElement')
            ->once()
            ->with('source')
            ->andReturns($audioSource);

        $audio->expects('appendChild')
            ->once()
            ->with($audioSource)
            ->andReturns($audioSource);

        $videoSource1 = m::mock(\DomElement::class);
        $videoSource1->expects('setAttribute')
            ->once()
            ->with('src', 'https://example.org/video.webm');
        $videoSource1->expects('setAttribute')
            ->once()
            ->with('type', 'video/webm');

        $this->dom->expects('createElement')
            ->once()
            ->with('source')
            ->andReturns($videoSource1);

        $this->video->expects('appendChild')
            ->once()
            ->with($videoSource1)
            ->andReturns($videoSource1);

        $videoSource2 = m::mock(\DomElement::class);
        $videoSource2->expects('setAttribute')
            ->once()
            ->with('src', 'https://example.org/video.mp4');
        $videoSource2->expects('setAttribute')
            ->once()
            ->with('type', 'video/mp4');

        $this->dom->expects('createElement')
            ->once()
            ->with('source')
            ->andReturns($videoSource2);

        $this->video->expects('appendChild')
            ->once()
            ->with($videoSource2)
            ->andReturns($videoSource2);

        $media = new Video('video/webm', 'https://example.org/video.webm', 'https://example.org/audio.mp3');
        $media->addSource('video/mp4', 'https://example.org/video.mp4');
        $this->assertEquals($this->video, $media->toDomElement($this->dom));
    }
}
