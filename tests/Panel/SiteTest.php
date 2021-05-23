<?php

namespace Kirby\Panel;

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
}
