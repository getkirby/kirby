<?php

namespace Kirby\Cms;

use Kirby\Image\Image;

class FilePropsTest extends TestCase
{

    protected function defaults(): array
    {
        return [
            'filename' => 'cover.jpg',
            'url'      => 'https://getkirby.com/projects/project-a/cover.jpg'
        ];
    }

    protected function file(array $props = [])
    {
        return new File(array_merge($this->defaults(), $props));
    }

    public function testAsset()
    {
        $file = $this->file();

        $this->assertInstanceOf(Image::class, $file->asset());
        $this->assertEquals($file->url(), $file->asset()->url());
    }

    public function testCollection()
    {
        $file = $this->file([
            'collection' => $files = new Files()
        ]);

        $this->assertInstanceOf(Files::class, $file->collection());
    }

    public function testContent()
    {
        $file = $this->file([
            'content' => $content = [
                'test' => 'Test'
            ]
        ]);

        $this->assertEquals('Test', $file->content()->get('test')->value());
    }

    public function testDefaultContent()
    {
        $file = $this->file();

        $this->assertInstanceOf(Content::class, $file->content());
    }

    public function testFilename()
    {
        $this->assertEquals($this->defaults()['filename'], $this->file()->filename());
    }

    public function testPage()
    {
        $file = $this->file([
            'parent' => $page = new Page(['slug' => 'test'])
        ]);

        $this->assertEquals($page, $file->page());
    }

    public function testDefaultPage()
    {
        return $this->assertNull($this->file()->page());
    }

    public function testUrl()
    {
        $this->assertEquals($this->defaults()['url'], $this->file()->url());
    }

    public function testOriginal()
    {
        $file = $this->file([
            'original' => $original = new File([
                'filename' => 'test',
                'url'      => 'test.jpg'
            ])
        ]);

        $this->assertEquals($original, $file->original());
    }

    public function testDefaultOriginal()
    {
        return $this->assertNull($this->file()->original());
    }

}
