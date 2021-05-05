<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase;

class UsersApiCollectionTest extends TestCase
{
    protected $api;
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'users' => [
                ['email' => 'a@getkirby.com'],
                ['email' => 'b@getkirby.com']
            ]
        ]);

        $this->api = $this->app->api();
    }

    public function testDefaultCollection()
    {
        $collection = $this->api->collection('users');
        $result     = $collection->toArray();

        $this->assertCount(2, $result);
        $this->assertEquals('a@getkirby.com', $result[0]['email']);
        $this->assertEquals('b@getkirby.com', $result[1]['email']);
    }

    public function testPassedCollection()
    {
        $collection = $this->api->collection('users', $this->app->users()->offset(1));
        $result     = $collection->toArray();

        $this->assertCount(1, $result);
        $this->assertEquals('b@getkirby.com', $result[0]['email']);
    }
}
