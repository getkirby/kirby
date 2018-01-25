<?php

namespace Kirby\Cms;

use Kirby\Image\Image;

class FilePropsTest extends TestCase
{

    protected function defaults(): array
    {
        return [
            'id'   => 'projects/project-a/cover.jpg',
            'root' => '/var/www/content/projects/project-a/cover.jpg',
            'url'  => 'https://getkirby.com/projects/project-a/cover.jpg'
        ];
    }

    protected function file(array $props = [])
    {
        return new File(array_merge($this->defaults(), $props));
    }

    public function testAsset()
    {
        $file = $this->file([
            'asset' => $asset = new Image('/test/cover.jpg', 'https://cdn.getkirby.com/test/cover.jpg')
        ]);

        $this->assertEquals($asset, $file->asset());
        $this->assertEquals($asset->root(), $file->asset()->root());
        $this->assertEquals($asset->url(), $file->asset()->url());
    }

    public function testDefaultAsset()
    {
        $file = $this->file();

        $this->assertInstanceOf(Image::class, $file->asset());
        $this->assertEquals($file->root(), $file->asset()->root());
        $this->assertEquals($file->url(), $file->asset()->url());
    }

    public function testCollection()
    {
        $file = $this->file([
            'collection' => $files = new Files()
        ]);

        $this->assertInstanceOf(Files::class, $file->collection());
    }

    public function testDefaultCollection()
    {
        $this->markTestIncomplete();
    }

    public function testContent()
    {
        $file = $this->file([
            'content' => $content = new Content([
                'test' => 'Test'
            ])
        ]);

        $this->assertEquals('Test', $file->content()->get('test')->value());
    }

    public function testDefaultContent()
    {
        $file = $this->file();

        $this->assertInstanceOf(Content::class, $file->content());
    }

    public function testId()
    {
        $this->assertEquals($this->defaults()['id'], $this->file()->id());
    }

    public function testMissingId()
    {
        $this->markTestIncomplete();
    }

    public function testPage()
    {
        $file = $this->file([
            'parent' => $page = new Page(['id' => 'test'])
        ]);

        $this->assertEquals($page, $file->page());
    }

    public function testDefaultPage()
    {
        return $this->assertNull($this->file()->page());
    }

    public function testRoot()
    {
        $this->assertEquals($this->defaults()['root'], $this->file()->root());
    }

    public function testMissingRoot()
    {
        $this->markTestIncomplete();
    }

    public function testUrl()
    {
        $this->assertEquals($this->defaults()['url'], $this->file()->url());
    }

    public function testMissingUrl()
    {
        $this->markTestIncomplete();
    }

    public function testOriginal()
    {
        $file = $this->file([
            'original' => $original = new File([
                'id'   => 'test',
                'root' => 'test.jpg',
                'url'  => 'test.jpg'
            ])
        ]);

        $this->assertEquals($original, $file->original());
    }

    public function testDefaultOriginal()
    {
        return $this->assertNull($this->file()->original());
    }

}
