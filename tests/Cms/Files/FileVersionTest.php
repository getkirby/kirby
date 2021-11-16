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

    public function tearDown(): void
    {
        Dir::remove(__DIR__ . '/tmp');
    }

    public function testConstruct()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $original = new File([
            'filename' => 'test.jpg',
            'parent' => $page,
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

    public function testExists()
    {
        $page = new Page([
            'root' => __DIR__ . '/fixtures/files',
            'slug' => 'files'
        ]);

        $original = new File([
            'filename' => 'test.jpg',
            'parent'   => $page
        ]);

        $version = new FileVersion([
            'original' => $original,
            'root'     => __DIR__ . '/tmp/test-version.jpg',
            'modifications' => [
                'width' => 50,
            ]
        ]);

        $this->assertFalse($version->exists());

        // calling an asset method will trigger
        // `FileVersion::save()` if it doesn't
        // exist yet
        $this->assertSame(50, $version->width());

        $this->assertTrue($version->exists());
    }

    public function testToArray()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $original = new File([
            'filename' => 'test.jpg',
            'parent' => $page,
            'content' => [
                'title' => 'Test Title'
            ]
        ]);

        $version = new FileVersion([
            'original' => $original,
            'root'     => __DIR__ . '/fixtures/files/test.jpg'
        ]);

        $this->assertSame('jpg', $version->toArray()['extension']);
        $this->assertSame(1192, $version->toArray()['size']);
    }

    public function testToString()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $original = new File(['filename' => 'test.jpg', 'parent' => $page]);
        $version  = new FileVersion([
            'original' => $original,
            'root'     => __DIR__ . '/fixtures/files/test.txt',
            'url'      => $url = 'https://assets.getkirby.com/test-200x200.txt',
        ]);

        $this->assertSame($url, (string)$version);

        $version  = new FileVersion([
            'original' => $original,
            'root'     => __DIR__ . '/fixtures/files/test.jpg',
            'url'      => $url = 'https://assets.getkirby.com/test-200x200.jpg',
        ]);

        $this->assertSame('<img alt="" src="' . $url . '">', (string)$version);
    }
}
