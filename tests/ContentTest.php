<?php

declare(strict_types=1);

namespace RedditImage\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use RedditImage\Content;
use RedditImage\Exception\InvalidContentException;

/**
 * @covers Content
 */
final class ContentTest extends TestCase
{
    /**
     * @dataProvider provideValidContent
     */
    public function testValidContent(
        string $content,
        string $expectedContentLink,
        string $expectedCommentLink,
        string $expectedPreprocessed,
        string $expectedMetadata,
        string $expectedRaw,
        string $expectedReal,
    ): void {
        $content = new Content($content);

        $this->assertEquals($expectedContentLink, $content->getContentLink(), 'Content links do not match');
        $this->assertEquals($expectedCommentLink, $content->getCommentsLink(), 'Comments links do not match');
        $this->assertEquals($expectedPreprocessed, $content->getPreprocessed(), 'Preprocessed contents do not match');
        $this->assertEquals($expectedMetadata, $content->getMetadata(), 'Metadata do not match');
        $this->assertEquals($expectedRaw, $content->getRaw(), 'Raw contents do not match');
        $this->assertEquals($expectedReal, $content->getReal(), 'Real contents do not match');
    }

    public static function provideValidContent(): \Generator
    {
        yield from self::provideUnprocessedTextOnlyContent();
        yield from self::provideUnprocessedImageOnlyContent();
        yield from self::provideUnprocessedImageAndTextContent();
        yield from self::providePreprocessedImageOnlyContent();
        yield from self::providePreprocessedImageAndTextContent();
    }

    private static function provideUnprocessedTextOnlyContent(): \Generator
    {
        yield 'unprocessed text only content' => [
            'content' => '<div data-sanitized-class="md"><p>I am building a fly rod (7\'6" glass 4 wt) and am just about done. Looks sick but I feel like it\'s missing a little something. Has anyone tried doing a sharpie design on the cork grip? Would the Sharpie rub off or bleed?</p> </div>   submitted by   <a href="https://www.reddit.com/user/ScoochSnail"> /u/ScoochSnail </a>   to   <a href="https://www.reddit.com/r/rodbuilding/"> r/rodbuilding </a> <br> <span><a href="https://www.reddit.com/r/rodbuilding/comments/106mc1g/cork_question/">[link]</a></span>   <span><a href="https://www.reddit.com/r/rodbuilding/comments/106mc1g/cork_question/">[comments]</a></span>',
            'content_link' => 'https://www.reddit.com/r/rodbuilding/comments/106mc1g/cork_question/',
            'comment_link' => 'https://www.reddit.com/r/rodbuilding/comments/106mc1g/cork_question/',
            'preprocessed' => '',
            'metadata' => '   submitted by   <a href="https://www.reddit.com/user/ScoochSnail"> /u/ScoochSnail </a>   to   <a href="https://www.reddit.com/r/rodbuilding/"> r/rodbuilding </a> <br> <span><a href="https://www.reddit.com/r/rodbuilding/comments/106mc1g/cork_question/">[link]</a></span>   <span><a href="https://www.reddit.com/r/rodbuilding/comments/106mc1g/cork_question/">[comments]</a></span>',
            'raw' => '<div data-sanitized-class="md"><p>I am building a fly rod (7\'6" glass 4 wt) and am just about done. Looks sick but I feel like it\'s missing a little something. Has anyone tried doing a sharpie design on the cork grip? Would the Sharpie rub off or bleed?</p> </div>   submitted by   <a href="https://www.reddit.com/user/ScoochSnail"> /u/ScoochSnail </a>   to   <a href="https://www.reddit.com/r/rodbuilding/"> r/rodbuilding </a> <br> <span><a href="https://www.reddit.com/r/rodbuilding/comments/106mc1g/cork_question/">[link]</a></span>   <span><a href="https://www.reddit.com/r/rodbuilding/comments/106mc1g/cork_question/">[comments]</a></span>',
            'real' => '<div data-sanitized-class="md"><p>I am building a fly rod (7\'6" glass 4 wt) and am just about done. Looks sick but I feel like it\'s missing a little something. Has anyone tried doing a sharpie design on the cork grip? Would the Sharpie rub off or bleed?</p> </div>',
        ];
    }

    private static function provideUnprocessedImageOnlyContent(): \Generator
    {
        yield 'unprocessed image only content' => [
            'content' => '<table> <tr><td> <a href="https://www.reddit.com/r/quilting/comments/11j8u2d/background_time/"> <img src="https://preview.redd.it/dz2apbqti0ma1.jpg?width=640&amp;crop=smart&amp;auto=webp&amp;s=20678b4b9f103c3948f0dd69e6b669c9a0b9c4a1" alt="Background time!!!!" title="Background time!!!!"> </a> </td><td>   submitted by   <a href="https://www.reddit.com/user/ScoochSnail"> /u/ScoochSnail </a>   to   <a href="https://www.reddit.com/r/quilting/"> r/quilting </a> <br> <span><a href="https://i.redd.it/dz2apbqti0ma1.jpg">[link]</a></span>   <span><a href="https://www.reddit.com/r/quilting/comments/11j8u2d/background_time/">[comments]</a></span> </td></tr></table>',
            'content_link' => 'https://i.redd.it/dz2apbqti0ma1.jpg',
            'comment_link' => 'https://www.reddit.com/r/quilting/comments/11j8u2d/background_time/',
            'preprocessed' => '',
            'metadata' => '   submitted by   <a href="https://www.reddit.com/user/ScoochSnail"> /u/ScoochSnail </a>   to   <a href="https://www.reddit.com/r/quilting/"> r/quilting </a> <br> <span><a href="https://i.redd.it/dz2apbqti0ma1.jpg">[link]</a></span>   <span><a href="https://www.reddit.com/r/quilting/comments/11j8u2d/background_time/">[comments]</a></span>',
            'raw' => '<table> <tr><td> <a href="https://www.reddit.com/r/quilting/comments/11j8u2d/background_time/"> <img src="https://preview.redd.it/dz2apbqti0ma1.jpg?width=640&amp;crop=smart&amp;auto=webp&amp;s=20678b4b9f103c3948f0dd69e6b669c9a0b9c4a1" alt="Background time!!!!" title="Background time!!!!"> </a> </td><td>   submitted by   <a href="https://www.reddit.com/user/ScoochSnail"> /u/ScoochSnail </a>   to   <a href="https://www.reddit.com/r/quilting/"> r/quilting </a> <br> <span><a href="https://i.redd.it/dz2apbqti0ma1.jpg">[link]</a></span>   <span><a href="https://www.reddit.com/r/quilting/comments/11j8u2d/background_time/">[comments]</a></span> </td></tr></table>',
            'real' => '',
        ];
    }

    private static function provideUnprocessedImageAndTextContent(): \Generator
    {
        yield 'unprocessed image and text content' => [
            'content' => '<table> <tr><td> <a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/"> <img src="https://preview.redd.it/yplmcb575lla1.jpg?width=640&amp;crop=smart&amp;auto=webp&amp;s=760bb3194e0d21bc70f1f78de7c1599d91764ed5" alt="Ferris wheel with an iPhone (2806x3983)" title="Ferris wheel with an iPhone (2806x3983)"> </a> </td><td> <div data-sanitized-class="md"><p>iPhone 11 Pro Max back camera 4.25mm f/1.8 Using Even Longer App 2806 x 3983 (3024 × 4032)</p> </div>   submitted by   <a href="https://www.reddit.com/user/Leading_Sort6644"> /u/Leading_Sort6644 </a>   to   <a href="https://www.reddit.com/r/ExposurePorn/"> r/ExposurePorn </a> <br> <span><a href="https://i.redd.it/yplmcb575lla1.jpg">[link]</a></span>   <span><a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/">[comments]</a></span> </td></tr></table>',
            'content_link' => 'https://i.redd.it/yplmcb575lla1.jpg',
            'comment_link' => 'https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/',
            'preprocessed' => '',
            'metadata' => '   submitted by   <a href="https://www.reddit.com/user/Leading_Sort6644"> /u/Leading_Sort6644 </a>   to   <a href="https://www.reddit.com/r/ExposurePorn/"> r/ExposurePorn </a> <br> <span><a href="https://i.redd.it/yplmcb575lla1.jpg">[link]</a></span>   <span><a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/">[comments]</a></span>',
            'raw' => '<table> <tr><td> <a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/"> <img src="https://preview.redd.it/yplmcb575lla1.jpg?width=640&amp;crop=smart&amp;auto=webp&amp;s=760bb3194e0d21bc70f1f78de7c1599d91764ed5" alt="Ferris wheel with an iPhone (2806x3983)" title="Ferris wheel with an iPhone (2806x3983)"> </a> </td><td> <div data-sanitized-class="md"><p>iPhone 11 Pro Max back camera 4.25mm f/1.8 Using Even Longer App 2806 x 3983 (3024 × 4032)</p> </div>   submitted by   <a href="https://www.reddit.com/user/Leading_Sort6644"> /u/Leading_Sort6644 </a>   to   <a href="https://www.reddit.com/r/ExposurePorn/"> r/ExposurePorn </a> <br> <span><a href="https://i.redd.it/yplmcb575lla1.jpg">[link]</a></span>   <span><a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/">[comments]</a></span> </td></tr></table>',
            'real' => '<div data-sanitized-class="md"><p>iPhone 11 Pro Max back camera 4.25mm f/1.8 Using Even Longer App 2806 x 3983 (3024 × 4032)</p> </div>',
        ];
    }

    private static function providePreprocessedImageOnlyContent(): \Generator
    {
        yield 'preprocessed image only content' => [
            'content' => '<div class="reddit-image figure"><!--xExtension-RedditImage | InsertTransformer | Image link--><img src="https://i.redd.it/dz2apbqti0ma1.jpg" class="reddit-image"></div>
                <table> <tr><td> <a href="https://www.reddit.com/r/quilting/comments/11j8u2d/background_time/"> <img src="https://preview.redd.it/dz2apbqti0ma1.jpg?width=640&amp;crop=smart&amp;auto=webp&amp;s=20678b4b9f103c3948f0dd69e6b669c9a0b9c4a1" alt="Background time!!!!" title="Background time!!!!"> </a> </td><td>   submitted by   <a href="https://www.reddit.com/user/ScoochSnail"> /u/ScoochSnail </a>   to   <a href="https://www.reddit.com/r/quilting/"> r/quilting </a> <br> <span><a href="https://i.redd.it/dz2apbqti0ma1.jpg">[link]</a></span>   <span><a href="https://www.reddit.com/r/quilting/comments/11j8u2d/background_time/">[comments]</a></span> </td></tr></table>',
            'content_link' => 'https://i.redd.it/dz2apbqti0ma1.jpg',
            'comment_link' => 'https://www.reddit.com/r/quilting/comments/11j8u2d/background_time/',
            'preprocessed' => '<div class="reddit-image figure"><!--xExtension-RedditImage | InsertTransformer | Image link--><img src="https://i.redd.it/dz2apbqti0ma1.jpg" class="reddit-image"></div>',
            'metadata' => '   submitted by   <a href="https://www.reddit.com/user/ScoochSnail"> /u/ScoochSnail </a>   to   <a href="https://www.reddit.com/r/quilting/"> r/quilting </a> <br> <span><a href="https://i.redd.it/dz2apbqti0ma1.jpg">[link]</a></span>   <span><a href="https://www.reddit.com/r/quilting/comments/11j8u2d/background_time/">[comments]</a></span>',
            'raw' => '<table> <tr><td> <a href="https://www.reddit.com/r/quilting/comments/11j8u2d/background_time/"> <img src="https://preview.redd.it/dz2apbqti0ma1.jpg?width=640&amp;crop=smart&amp;auto=webp&amp;s=20678b4b9f103c3948f0dd69e6b669c9a0b9c4a1" alt="Background time!!!!" title="Background time!!!!"> </a> </td><td>   submitted by   <a href="https://www.reddit.com/user/ScoochSnail"> /u/ScoochSnail </a>   to   <a href="https://www.reddit.com/r/quilting/"> r/quilting </a> <br> <span><a href="https://i.redd.it/dz2apbqti0ma1.jpg">[link]</a></span>   <span><a href="https://www.reddit.com/r/quilting/comments/11j8u2d/background_time/">[comments]</a></span> </td></tr></table>',
            'real' => '',
        ];
    }

    private static function providePreprocessedImageAndTextContent(): \Generator
    {
        yield 'preprocessed image and text content' => [
            'content' => '<div class="reddit-image figure"><!--xExtension-RedditImage | InsertTransformer | Image link--><img src="https://i.redd.it/yplmcb575lla1.jpg" class="reddit-image"></div>
                <table> <tr><td> <a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/"> <img src="https://preview.redd.it/yplmcb575lla1.jpg?width=640&amp;crop=smart&amp;auto=webp&amp;s=760bb3194e0d21bc70f1f78de7c1599d91764ed5" alt="Ferris wheel with an iPhone (2806x3983)" title="Ferris wheel with an iPhone (2806x3983)"> </a> </td><td> <div data-sanitized-class="md"><p>iPhone 11 Pro Max back camera 4.25mm f/1.8 Using Even Longer App 2806 x 3983 (3024 × 4032)</p> </div>   submitted by   <a href="https://www.reddit.com/user/Leading_Sort6644"> /u/Leading_Sort6644 </a>   to   <a href="https://www.reddit.com/r/ExposurePorn/"> r/ExposurePorn </a> <br> <span><a href="https://i.redd.it/yplmcb575lla1.jpg">[link]</a></span>   <span><a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/">[comments]</a></span> </td></tr></table>',
            'content_link' => 'https://i.redd.it/yplmcb575lla1.jpg',
            'comment_link' => 'https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/',
            'preprocessed' => '<div class="reddit-image figure"><!--xExtension-RedditImage | InsertTransformer | Image link--><img src="https://i.redd.it/yplmcb575lla1.jpg" class="reddit-image"></div>',
            'metadata' => '   submitted by   <a href="https://www.reddit.com/user/Leading_Sort6644"> /u/Leading_Sort6644 </a>   to   <a href="https://www.reddit.com/r/ExposurePorn/"> r/ExposurePorn </a> <br> <span><a href="https://i.redd.it/yplmcb575lla1.jpg">[link]</a></span>   <span><a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/">[comments]</a></span>',
            'raw' => '<table> <tr><td> <a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/"> <img src="https://preview.redd.it/yplmcb575lla1.jpg?width=640&amp;crop=smart&amp;auto=webp&amp;s=760bb3194e0d21bc70f1f78de7c1599d91764ed5" alt="Ferris wheel with an iPhone (2806x3983)" title="Ferris wheel with an iPhone (2806x3983)"> </a> </td><td> <div data-sanitized-class="md"><p>iPhone 11 Pro Max back camera 4.25mm f/1.8 Using Even Longer App 2806 x 3983 (3024 × 4032)</p> </div>   submitted by   <a href="https://www.reddit.com/user/Leading_Sort6644"> /u/Leading_Sort6644 </a>   to   <a href="https://www.reddit.com/r/ExposurePorn/"> r/ExposurePorn </a> <br> <span><a href="https://i.redd.it/yplmcb575lla1.jpg">[link]</a></span>   <span><a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/">[comments]</a></span> </td></tr></table>',
            'real' => '<div data-sanitized-class="md"><p>iPhone 11 Pro Max back camera 4.25mm f/1.8 Using Even Longer App 2806 x 3983 (3024 × 4032)</p> </div>',
        ];
    }

    /**
     * @dataProvider provideInvalidContent
     */
    public function testInvalidContent(string $content): void
    {
        $this->expectException(InvalidContentException::class);
        new Content($content);
    }

    public static function provideInvalidContent(): \Generator
    {
        yield 'no metadata, preprocessed content' => ['<div class="reddit-image figure"><!--xExtension-RedditImage/1.0.0 | RedditImage\Processor\BeforeInsertProcessor | RedditImage\Transformer\Agnostic\ImageTransformer--><img src="https://i.redd.it/yplmcb575lla1.jpg" class="reddit-image"></div> <span><a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/">[comments]</a></span>'];
        yield 'no metadata, no preprocessed content' => ['<span><a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/">[comments]</a></span>'];
        yield 'no content link' => ['   submitted by   <a href="https://www.reddit.com/user/Leading_Sort6644"> /u/Leading_Sort6644 </a>   to   <a href="https://www.reddit.com/r/ExposurePorn/"> r/ExposurePorn </a> <br> <span><a href="https://i.redd.it/yplmcb575lla1.jpg">[invalid]</a></span>   <span><a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/">[comments]</a></span>'];
        yield 'no comments link' => ['   submitted by   <a href="https://www.reddit.com/user/Leading_Sort6644"> /u/Leading_Sort6644 </a>   to   <a href="https://www.reddit.com/r/ExposurePorn/"> r/ExposurePorn </a> <br> <span><a href="https://i.redd.it/yplmcb575lla1.jpg">[link]</a></span>   <span><a href="https://www.reddit.com/r/ExposurePorn/comments/11h3r50/ferris_wheel_with_an_iphone_2806x3983/">[invalid]</a></span>'];
    }
}
