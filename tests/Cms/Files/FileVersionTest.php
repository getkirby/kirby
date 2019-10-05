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
            'filename' => 'test.jpg'
        ]);

        $version = new FileVersion([
            'original'      => $original,
            'url'           => $url = 'https://assets.getkirby.com/test-200x200.jpg',
            'modifications' => $mods = [
                'width' => 200,
                'height' => 200
            ]
        ]);

        $this->assertEquals($url, $version->url());
        $this->assertEquals($mods, $version->modifications());
        $this->assertEquals($original, $version->original());
        $this->assertEquals($original->kirby(), $version->kirby());
    }
}
