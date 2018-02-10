<?php

namespace Kirby\Cms;

class PageFilesTest extends TestCase
{

    public function testDefaultFiles()
    {
        $page = new Page(['slug' => 'test']);
        $this->assertInstanceOf(Files::class, $page->files());
        $this->assertCount(0, $page->files());
    }

    public function testFiles()
    {
        $files = new Files([
            $file = new File(['filename' => 'test.jpg', 'url' => '/test.jpg'])
        ]);

        $page = new Page([
            'slug'  => 'test',
            'files' => $files
        ]);

        $this->assertInstanceOf(Files::class, $page->files());
        $this->assertCount(1, $page->files());
    }

}
