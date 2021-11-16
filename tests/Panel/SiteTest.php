<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Site as ModelSite;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Site
 */
class SiteTest extends TestCase
{
    protected $app;
    protected $tmp = __DIR__ . '/tmp';

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->tmp,
            ]
        ]);

        Dir::make($this->tmp);
    }

    public function tearDown(): void
    {
        Dir::remove($this->tmp);
    }

    protected function panel(array $props = [])
    {
        $site = new ModelSite($props);
        return new Site($site);
    }

    /**
     * @covers ::dropdownOption
     */
    public function testDropdownOption(): void
    {
        $model = $this->panel([
            'content' => [
                'title' => 'Test site'
            ]
        ]);

        $option = $model->dropdownOption();

        $this->assertSame('home', $option['icon']);
        $this->assertSame('Test site', $option['text']);
        $this->assertSame('/panel/site', $option['link']);
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
            'back' => 'pattern',
            'color' => 'gray-500',
            'cover' => false,
            'icon' => 'page',
            'ratio' => '3/2',
            'url' => $mediaUrl . '/test.jpg',
            'src' => Model::imagePlaceholder(),
            'srcset' => $mediaUrl . '/test-38x.jpg 38w, ' . $mediaUrl . '/test-76x.jpg 76w'
        ], $panel->image());

        // cover enabled
        $this->assertSame([
            'back' => 'pattern',
            'color' => 'gray-500',
            'cover' => true,
            'icon' => 'page',
            'ratio' => '3/2',
            'url' => $mediaUrl . '/test.jpg',
            'src' => Model::imagePlaceholder(),
            'srcset' => $mediaUrl . '/test-38x38-crop.jpg 1x, ' . $mediaUrl . '/test-76x76-crop.jpg 2x'
        ], $panel->image(['cover' => true]));
    }

    /**
     * @covers ::path
     */
    public function testPath()
    {
        $this->assertSame('site', $this->panel()->path());
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
     * @covers ::view
     */
    public function testView()
    {
        $view = $this->panel()->view();
        $this->assertArrayHasKey('props', $view);
        $this->assertSame('k-site-view', $view['component']);
    }
}
