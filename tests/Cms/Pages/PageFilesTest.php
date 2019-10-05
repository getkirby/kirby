<?php

namespace Kirby\Cms;

class PageFilesTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/PageFilesTest'
            ]
        ]);
    }

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

    public function testImages()
    {
        $page = new Page([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'test.ai'],
                ['filename' => 'test.bmp'],
                ['filename' => 'test.gif'],
                ['filename' => 'test.eps'],
                ['filename' => 'test.ico'],
                ['filename' => 'test.jpeg'],
                ['filename' => 'test.jpg'],
                ['filename' => 'test.jpe'],
                ['filename' => 'test.png'],
                ['filename' => 'test.ps'],
                ['filename' => 'test.psd'],
                ['filename' => 'test.svg'],
                ['filename' => 'test.tif'],
                ['filename' => 'test.tiff'],
                ['filename' => 'test.webp'],
                ['filename' => 'test.txt'],
                ['filename' => 'test.doc'],
            ]
        ]);

        $this->assertInstanceOf(Files::class, $page->images());
        $this->assertCount(15, $page->images());
    }
}
