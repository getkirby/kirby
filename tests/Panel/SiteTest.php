<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Site as ModelSite;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Site
 */
class SiteTest extends TestCase
{
    public function tearDown(): void
    {
        Dir::remove(__DIR__ . '/tmp');
    }

    /**
     * @covers ::path
     * @covers \Kirby\Panel\Model::path
     */
    public function testPath()
    {
        $site  = new ModelSite();
        $panel = new Site($site);
        $this->assertSame('site', $panel->path());
    }

    /**
     * @covers \Kirby\Panel\Model::url
     */
    public function testUrl()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $site  = new ModelSite();
        $panel = new Site($site);
        $this->assertSame('/panel/site', $panel->url());
        $this->assertSame('/site', $panel->url(true));
    }

    /**
     * @covers ::imageSource
     * @covers \Kirby\Panel\Model::image
     * @covers \Kirby\Panel\Model::imageSource
     */
    public function testImage()
    {
        $site = new ModelSite([
            'files' => [
                ['filename' => 'test.jpg']
            ]
        ]);

        // fallback to model itself
        $image = (new Site($site))->image();
        $this->assertTrue(Str::endsWith($image['url'], '/test.jpg'));
    }

    /**
     * @covers ::imageSource
     * @covers \Kirby\Panel\Model::image
     * @covers \Kirby\Panel\Model::imageSource
     */
    public function testImageCover()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null',
                'media' => __DIR__ . '/tmp'
            ],
            'site' => [
                'files' => [
                    ['filename' => 'test.jpg']
                ]
            ]
        ]);

        $site  = $app->site();
        $panel = new Site($site);

        $hash = $site->image()->mediaHash();
        $mediaUrl = $site->mediaUrl() . '/' . $hash;

        // cover disabled as default
        $this->assertSame([
            'ratio' => '3/2',
            'back' => 'pattern',
            'cover' => false,
            'url' => $mediaUrl . '/test.jpg',
            'cards' => [
                'url' => Model::imagePlaceholder(),
                'srcset' => $mediaUrl . '/test-352x.jpg 352w, ' . $mediaUrl . '/test-864x.jpg 864w, ' . $mediaUrl . '/test-1408x.jpg 1408w'
            ],
            'list' => [
                'url' => Model::imagePlaceholder(),
                'srcset' => $mediaUrl . '/test-38x.jpg 38w, ' . $mediaUrl . '/test-76x.jpg 76w'
            ]
        ], $panel->image());

        // cover enabled
        $this->assertSame([
            'ratio' => '3/2',
            'back' => 'pattern',
            'cover' => true,
            'url' => $mediaUrl . '/test.jpg',
            'cards' => [
                'url' => Model::imagePlaceholder(),
                'srcset' => $mediaUrl . '/test-352x.jpg 352w, ' . $mediaUrl . '/test-864x.jpg 864w, ' . $mediaUrl . '/test-1408x.jpg 1408w'
            ],
            'list' => [
                'url' => Model::imagePlaceholder(),
                'srcset' => $mediaUrl . '/test-38x38.jpg 1x, ' . $mediaUrl . '/test-76x76.jpg 2x'
            ]
        ], $panel->image(['cover' => true]));
    }
}
