<?php

namespace Kirby\Cms;

class PageFilesTest extends TestCase
{

    public function testDefaultFiles()
    {
        $page = new Page(['id' => 'test']);
        $this->assertInstanceOf(Files::class, $page->files());
        $this->assertCount(0, $page->files());
    }

    public function testFiles()
    {
        $files = new Files([
            $file = new File(['id' => 'test', 'root' => '/test.jpg', 'url' => '/test.jpg'])
        ]);

        $page = new Page([
            'id'    => 'test',
            'files' => $files
        ]);

        $this->assertInstanceOf(Files::class, $page->files());
        $this->assertCount(1, $page->files());
    }

}
