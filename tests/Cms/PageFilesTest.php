<?php

namespace Kirby\Cms;

class PageFilesTest extends TestCase
{

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testDefaultFilesWithoutStore()
    {
        $page = new Page(['id' => 'test']);
        $this->assertInstanceOf(Files::class, $page->files());
    }

    public function testDefaultFilesWithStore()
    {
        $store = new Store([
            'page.files' => function ($page) {
                return new Files([], $page);
            }
        ]);

        $page = new Page([
            'id'    => 'test',
            'store' => $store
        ]);

        $this->assertInstanceOf(Files::class, $page->files());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "files" property must be of type "Kirby\Cms\Files"
     */
    public function testInvalidFiles()
    {
        $page = new Page([
            'id'    => 'test',
            'files' => 'files'
        ]);
    }

    public function testFiles()
    {
        $files = new Files([]);
        $page  = new Page([
            'id'    => 'test',
            'files' => $files
        ]);
        $this->assertEquals($files, $page->files());
    }

}
