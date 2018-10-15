<?php

namespace Kirby\Cms;

use Kirby\Image\Image;
use Kirby\Toolkit\F;

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

    public function testDragText()
    {
        $page = new Page([
            'slug'  => 'test',
            'files' => [
                [
                    'filename' => 'test.pdf'
                ]
            ]
        ]);

        $file = $page->file('test.pdf');

        $this->assertEquals('(file: test.pdf)', $file->dragText());
        $this->assertEquals('[test.pdf](/media/pages/test/test.pdf)', $file->dragText('markdown'));
    }

    public function testDragTextForImages()
    {
        $page = new Page([
            'slug'  => 'test',
            'files' => [
                [
                    'filename' => 'test.jpg'
                ]
            ]
        ]);

        $file = $page->file('test.jpg');

        $this->assertEquals('(image: test.jpg)', $file->dragText());
        $this->assertEquals('![](/media/pages/test/test.jpg)', $file->dragText('markdown'));
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

    public function testToString()
    {
        $file = new File(['filename' => 'super.jpg']);
        $this->assertEquals('super.jpg', $file->toString('{{ file.filename }}'));
    }

    public function testModified()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/FilePropsTest/modified',
                'content' => $index
            ]
        ]);

        // create a site file
        F::write($file = $index . '/test.js', 'test');

        $modified = filemtime($file);
        $file     = $app->file('test.js');

        $this->assertEquals($modified, $file->modified());

        // default date handler
        $format = 'd.m.Y';
        $this->assertEquals(date($format, $modified), $file->modified($format));

        // custom date handler
        $format = '%d.%m.%Y';
        $this->assertEquals(strftime($format, $modified), $file->modified($format, 'strftime'));

        Dir::remove($index);
    }

}
