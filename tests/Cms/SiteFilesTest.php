<?php

namespace Kirby\Cms;

class SiteFilesTest extends TestCase
{

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testDefaultFilesWithoutStore()
    {
        $site = new Site();
        $this->assertInstanceOf(Files::class, $site->files());
    }

    public function testDefaultFilesWithStore()
    {
        $store = new Store([
            'site.files' => function ($site) {
                return new Files([], $site);
            }
        ]);

        $site = new Site([
            'store' => $store
        ]);

        $this->assertInstanceOf(Files::class, $site->files());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "files" property must be of type "Kirby\Cms\Files"
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
