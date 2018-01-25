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
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Site::setFiles() must be an instance of Kirby\Cms\Files or null, string given
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
