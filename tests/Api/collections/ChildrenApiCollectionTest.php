<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Cms\Site;
use PHPUnit\Framework\TestCase;

class ChildrenApiCollectionTest extends TestCase
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
        $site = new Site([
            'children' => [
                ['slug' => 'a'],
                ['slug' => 'b'],
            ]
        ]);

        $collection = $this->api->collection('children', $site->children());
        $result     = $collection->toArray();

        $this->assertCount(2, $result);
        $this->assertEquals('a', $result[0]['id']);
        $this->assertEquals('b', $result[1]['id']);
    }
}
