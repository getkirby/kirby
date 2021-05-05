<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Files;
use PHPUnit\Framework\TestCase;

class FilesApiCollectionTest extends TestCase
{
    protected $api;
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
        ]);

        $this->api = $this->app->api();
    }

    public function testCollection()
    {
        $collection = $this->api->collection('files', new Files([
            new File(['filename' => 'a.jpg']),
            new File(['filename' => 'b.jpg'])
        ]));

        $result = $collection->toArray();

        $this->assertCount(2, $result);
        $this->assertEquals('a.jpg', $result[0]['filename']);
        $this->assertEquals('b.jpg', $result[1]['filename']);
    }
}
