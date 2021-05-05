<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase;

class LanguagesApiCollectionTest extends TestCase
{
    protected $api;
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'languages' => [
                [
                    'code' => 'en',
                    'default' => true
                ],
                [
                    'code' => 'de',
                ]
            ]
        ]);

        $this->api = $this->app->api();
    }

    public function testCollection()
    {
        $collection = $this->api->collection('languages', $this->app->languages());
        $result     = $collection->toArray();

        $this->assertCount(2, $result);
        $this->assertEquals('en', $result[0]['code']);
        $this->assertEquals('de', $result[1]['code']);
    }
}
