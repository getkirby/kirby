<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase;

class RolesApiCollectionTest extends TestCase
{
    protected $api;
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'roles' => [
                [
                    'name' => 'admin',
                ],
                [
                    'name' => 'editor',
                ]
            ]
        ]);

        $this->api = $this->app->api();
    }

    public function testCollection()
    {
        $collection = $this->api->collection('roles', $this->app->roles());
        $result     = $collection->toArray();

        $this->assertCount(2, $result);
        $this->assertEquals('admin', $result[0]['name']);
        $this->assertEquals('editor', $result[1]['name']);
    }
}
