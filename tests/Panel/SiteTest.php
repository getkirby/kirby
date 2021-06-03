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
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/SiteTest',
            ]
        ]);

        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    protected function panel(array $props = [])
    {
        $site = new ModelSite($props);
        return new Site($site);
    }

    /**
     * @covers ::path
     */
    public function testPath()
    {
        $this->assertSame('site', $this->panel()->path());
    }

    /**
     * @covers ::imageSource
     */
    public function testImage()
    {
        $panel = $this->panel([
            'files' => [
                ['filename' => 'test.jpg']
            ]
        ]);

        // fallback to model itself
        $image = $panel->image();
        $this->assertTrue(Str::endsWith($image['url'], '/test.jpg'));
    }

    /**
     * @covers ::imageSource
     * @covers \Kirby\Panel\Model::image
     * @covers \Kirby\Panel\Model::imageSource
     */
    public function testImageCover()
    {
        $app = $this->app->clone([
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

    /**
     * @covers ::props
     */
    public function testProps()
    {
        $props = $this->panel()->props();

        $this->assertArrayHasKey('model', $props);
        $this->assertArrayHasKey('content', $props['model']);
        $this->assertArrayHasKey('previewUrl', $props['model']);
        $this->assertArrayHasKey('title', $props['model']);

        // inherited props
        $this->assertArrayHasKey('blueprint', $props);
        $this->assertArrayHasKey('lock', $props);
        $this->assertArrayHasKey('permissions', $props);
        $this->assertArrayHasKey('tab', $props);
        $this->assertArrayHasKey('tabs', $props);
    }

    /**
     * @covers ::route
     */
    public function testRoute()
    {
        $route = $this->panel()->route();
        $this->assertArrayHasKey('props', $route);
        $this->assertSame('k-site-view', $route['component']);
    }
}
