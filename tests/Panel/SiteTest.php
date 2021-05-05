<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Site as ModelSite;
use PHPUnit\Framework\TestCase;

class SiteTest extends TestCase
{
    public function testPath()
    {
        $site  = new ModelSite();
        $panel = new Site($site);
        $this->assertSame('site', $panel->path());
    }

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
