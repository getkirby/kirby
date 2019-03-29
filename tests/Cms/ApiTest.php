<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;

class ApiTest extends TestCase
{
    public function setUp(): void
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
                        ],
                        'files' => [
                            [
                                'filename' => 'a-regular-file.jpg',
                            ],
                            [
                                'filename' => 'a filename with spaces.jpg',
                            ]
                        ]
                    ],
                    [
                        'slug' => 'b'
                    ]
                ]
            ],
            'options' => [
                'api' => [
                    'authentication' => function () {
                        return true;
                    }
                ]
            ],
        ]);

        $this->app->impersonate('kirby');
        $this->api = $this->app->api();
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testLanguage()
    {
        $api = $this->api->clone([
            'requestData' => [
                'headers' => [
                    'x-language' => 'de'
                ]
            ]
        ]);

        $this->assertEquals('de', $api->language());
    }

    public function testFile()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug'  => 'a',
                        'files' => [
                            ['filename' => 'test.jpg']
                        ],
                        'children' => [
                            [
                                'slug' => 'a',
                                'files' => [
                                    ['filename' => 'test.jpg']
                                ],
                            ]
                        ]
                    ]
                ],
                'files' => [
                    ['filename' => 'test.jpg']
                ]
            ],
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'files' => [
                        ['filename' => 'test.jpg']
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');
        $api = $app->api();

        $this->assertEquals('test.jpg', $api->file('site', 'test.jpg')->filename());
        $this->assertEquals('test.jpg', $api->file('pages/a', 'test.jpg')->filename());
        $this->assertEquals('test.jpg', $api->file('pages/a+a', 'test.jpg')->filename());
        $this->assertEquals('test.jpg', $api->file('users/test@getkirby.com', 'test.jpg')->filename());

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The file "nope.jpg" cannot be found');
        $this->api->file('site', 'nope.jpg');
    }

    public function testPage()
    {
        $a  = $this->app->page('a');
        $aa = $this->app->page('a/aa');

        $this->assertEquals($a, $this->api->page('a'));
        $this->assertEquals($aa, $this->api->page('a+aa'));

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The page "does-not-exist" cannot be found');
        $this->api->page('does-not-exist');
    }

    public function testUser()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'current@getkirby.com',
                ],
                [
                    'email' => 'test@getkirby.com',
                ]
            ],
        ]);

        $app->impersonate('current@getkirby.com');
        $api = $app->api();

        $this->assertEquals('current@getkirby.com', $api->user()->email());
        $this->assertEquals('test@getkirby.com', $api->user('test@getkirby.com')->email());

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The user "nope@getkirby.com" cannot be found');
        $this->api->user('nope@getkirby.com');
    }

    public function testUsers()
    {
        $this->assertEquals($this->app->users(), $this->api->users());
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

    public function testFileGetRoute()
    {

        // regular
        $result = $this->api->call('pages/a/files/a-regular-file.jpg', 'GET');

        $this->assertEquals(200, $result['code']);
        $this->assertEquals('a-regular-file.jpg', $result['data']['filename']);

        // with spaces in filename
        $result = $this->api->call('pages/a/files/a filename with spaces.jpg', 'GET');

        $this->assertEquals(200, $result['code']);
        $this->assertEquals('a filename with spaces.jpg', $result['data']['filename']);
    }

    public function testAuthenticationWithoutCsrf()
    {
        $auth = $this->createMock(Auth::class);
        $auth->method('type')->willReturn('session');
        $auth->method('csrf')->willReturn(false);

        $kirby = $this->createMock(App::class);
        $kirby->method('auth')->willReturn($auth);

        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('Unauthenticated');

        $function = require $this->app->root('kirby') . '/config/api/authentication.php';

        $api = new Api([
            'kirby' => $kirby
        ]);

        $function->call($api);
    }

    public function testAuthenticationWithoutUser()
    {
        $auth = $this->createMock(Auth::class);
        $auth->method('user')->willReturn(null);

        $kirby = $this->createMock(App::class);
        $kirby->method('auth')->willReturn($auth);

        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('Unauthenticated');

        $function = require $this->app->root('kirby') . '/config/api/authentication.php';

        $api = new Api([
            'kirby' => $kirby
        ]);

        $function->call($api);
    }
}
