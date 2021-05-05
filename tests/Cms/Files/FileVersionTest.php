<?php

namespace Kirby\Cms;

class FileVersionTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
        ]);
    }

    public function testConstruct()
    {
        $original = new File([
            'filename' => 'test.jpg',
            'content' => [
                'title' => 'Test Title'
            ]
        ]);

        $version = new FileVersion([
            'original'      => $original,
            'url'           => $url = 'https://assets.getkirby.com/test-200x200.jpg',
            'modifications' => $mods = [
                'width' => 200,
                'height' => 200
            ]
        ]);

        $this->assertSame($url, $version->url());
        $this->assertSame('Test Title', $version->title()->value());
        $this->assertSame($mods, $version->modifications());
        $this->assertSame($original, $version->original());
        $this->assertSame($original->kirby(), $version->kirby());
    }

    public function testToArray()
    {
        $original = new File([
            'filename' => 'test.jpg',
            'content' => [
                'title' => 'Test Title'
            ]
        ]);

        $version = new FileVersion([
            'original'      => $original,
            'root'          => __DIR__ . '/fixtures/files/test.jpg'
        ]);

        $this->assertSame('jpg', $version->toArray()['extension']);
        $this->assertSame(1192, $version->toArray()['size']);
    }
}
