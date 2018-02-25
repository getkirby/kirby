<?php

namespace Kirby\Cms;

class SiteFilesTest extends TestCase
{

    public function testDefaultFiles()
    {
        $site = new Site();
        $this->assertInstanceOf(Files::class, $site->files());
    }

    /**
     * @expectedException TypeError
     */
    public function testInvalidFiles()
    {
        $site = new Site([
            'files' => 'files'
        ]);
    }

    public function testFiles()
    {
        $files = new Files([]);
        $site  = new Site([
            'files' => $files
        ]);

        $this->assertEquals($files, $site->files());
    }

}
