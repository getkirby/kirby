<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use PHPUnit\Framework\TestCase;

class PagesApiCollectionTest extends TestCase
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
        $collection = $this->api->collection('pages', new Pages([
            new Page(['slug' => 'a']),
            new Page(['slug' => 'b'])
        ]));

        $result = $collection->toArray();

        $this->assertCount(2, $result);
        $this->assertEquals('a', $result[0]['id']);
        $this->assertEquals('b', $result[1]['id']);
    }
}
