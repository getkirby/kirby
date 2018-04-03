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
        $page = new Page([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'test.jpg']
            ]
        ]);

        $this->assertInstanceOf(Files::class, $page->files());
        $this->assertCount(1, $page->files());
    }

}
