<?php

namespace Kirby\Cms;

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
        $page = new Page([
            'slug' => 'test'
        ]);

        $collection = $this->api->collection('files', new Files([
            new File(['filename' => 'a.jpg', 'parent' => $page]),
            new File(['filename' => 'b.jpg', 'parent' => $page])
        ]));

        $result = $collection->toArray();

        $this->assertCount(2, $result);
        $this->assertEquals('a.jpg', $result[0]['filename']);
        $this->assertEquals('b.jpg', $result[1]['filename']);
    }
}
