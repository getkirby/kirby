<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class AccountRoutesTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'options' => [
                'api.allowImpersonation' => true
            ],
            'roots' => [
                'index' => '/dev/null'
            ],
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin'
                ]
            ]
        ]);

        $this->app->impersonate('test@getkirby.com');
    }

    public function testGet()
    {
        $app = $this->app;

        $response = $app->api()->call('account');

        $this->assertEquals('test@getkirby.com', $response['data']['email']);
    }

    public function testFiles()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin',
                    'files' => [
                        [
                            'filename' => 'c.jpg',
                        ],
                        [
                            'filename' => 'a.jpg',
                        ],
                        [
                            'filename' => 'b.jpg',
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('test@getkirby.com');

        $response = $app->api()->call('account/files');

        $this->assertCount(3, $response['data']);
        $this->assertSame('a.jpg', $response['data'][0]['filename']);
        $this->assertSame('b.jpg', $response['data'][1]['filename']);
        $this->assertSame('c.jpg', $response['data'][2]['filename']);
    }

    public function testFilesSorted()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin',
                    'files' => [
                        [
                            'filename' => 'a.jpg',
                            'content'  => [
                                'sort' => 2
                            ]
                        ],
                        [
                            'filename' => 'b.jpg',
                            'content'  => [
                                'sort' => 1
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('test@getkirby.com');

        $response = $app->api()->call('account/files');

        $this->assertEquals('b.jpg', $response['data'][0]['filename']);
        $this->assertEquals('a.jpg', $response['data'][1]['filename']);
    }

    public function testFile()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin',
                    'files' => [
                        [
                            'filename' => 'a.jpg',
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('test@getkirby.com');

        $response = $app->api()->call('account/files/a.jpg');

        $this->assertEquals('a.jpg', $response['data']['filename']);
    }


    public function testAvatar()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin',
                    'files' => [
                        [
                            'filename' => 'profile.jpg',
                            'template' => 'avatar'
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('test@getkirby.com');

        $response = $app->api()->call('account/avatar');

        $this->assertEquals('profile.jpg', $response['data']['filename']);
    }
}
