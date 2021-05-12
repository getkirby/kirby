<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Site as ModelSite;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Site
 */
class SiteTest extends TestCase
{
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
}
