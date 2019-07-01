<?php

namespace Kirby\Cms;

class SiteFilesTest extends TestCase
{
    public function testDefaultFiles()
    {
        $site = new Site();
        $this->assertInstanceOf(Files::class, $site->files());
    }

    public function testInvalidFiles()
    {
        $this->expectException('TypeError');

        $site = new Site([
            'files' => 'files'
        ]);
    }

    public function testFiles()
    {
        $site  = new Site([
            'files' => []
        ]);

        $this->assertInstanceOf(Files::class, $site->files());
    }
}
