<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;

class ApiTest extends TestCase
{

    public function setUp()
    {

        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/ApiTest'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'children' => [
                            [
                                'slug' => 'aa'
                            ],
                            [
                                'slug' => 'ab'
                            ]
                        ]
                    ],
                    [
                        'slug' => 'b'
                    ]
                ]
            ]
        ]);

        $this->app->impersonate('kirby');

        $this->api = $this->app->api();

    }

    public function tearDown()
    {
        return Dir::remove($this->fixtures);
    }

    public function testSiteFind()
    {

        // find single
        $result = $this->api->call('site/find', 'POST', [
            'body' => [
                'a',
            ]
        ]);

        $this->assertCount(1, $result['data']);
        $this->assertEquals('a', $result['data'][0]['id']);

        // find multiple
        $result = $this->api->call('site/find', 'POST', [
            'body' => [
                'a',
                'a/aa',
                'b'
            ]
        ]);

        $this->assertCount(3, $result['data']);
        $this->assertEquals('a', $result['data'][0]['id']);
        $this->assertEquals('a/aa', $result['data'][1]['id']);
        $this->assertEquals('b', $result['data'][2]['id']);

    }

}
