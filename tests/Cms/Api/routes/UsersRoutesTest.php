<?php

namespace Kirby\Cms;

use Kirby\Cms\Section;
use Kirby\Form\Field;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase;

class UsersRoutesTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        Blueprint::$loaded = [];

        $this->app = new App([
            'options' => [
                'api.allowImpersonation' => true
            ],
            'roles' => [
                [
                    'name' => 'admin'
                ],
                [
                    'name' => 'editor'
                ]
            ],
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/UsersRoutesTest'
            ],
            'users' => [
                [
                    'name'  => 'Bastian',
                    'email' => 'admin@getkirby.com',
                    'role'  => 'admin'
                ],
                [
                    'name'  => 'Sonja',
                    'email' => 'editor@getkirby.com',
                    'role'  => 'admin'
                ]
            ]
        ]);

        $this->app->impersonate('kirby');
    }

    public function tearDown(): void
    {
        App::destroy();
        Field::$types = [];
        Section::$types = [];
        Dir::remove($this->fixtures);
    }

    public function testList()
    {
        $app = $this->app;

        $response = $app->api()->call('users');

        $this->assertEquals('admin@getkirby.com', $response['data'][0]['email']);
        $this->assertEquals('editor@getkirby.com', $response['data'][1]['email']);
    }

    public function testCreate()
    {
        $app = $this->app;

        $response = $app->api()->call('users', 'POST', [
            'body' => [
                'email' => 'test@getkirby.com',
                'role'  => 'admin'
            ]
        ]);

        $this->assertSame('ok', $response['status']);
        $this->assertSame('model', $response['type']);
        $this->assertSame('admin', $response['data']['role']['name']);
        $this->assertSame('test@getkirby.com', $response['data']['username']);
    }

    public function testGet()
    {
        $app = $this->app;

        $response = $app->api()->call('users/admin@getkirby.com');

        $this->assertEquals('admin@getkirby.com', $response['data']['email']);
    }

    public function testBlueprint()
    {
        $app = $this->app->clone([
            'blueprints' => [
                'users/admin' => [
                    'title' => 'Test'
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('users/admin@getkirby.com/blueprint');

        $this->assertSame(200, $response['code']);
        $this->assertSame('Test', $response['data']['title']);
        $this->assertSame('users/admin', $response['data']['name']);
    }

    public function testUpdate()
    {
        $app = $this->app;

        $response = $app->api()->call('users/admin@getkirby.com', 'PATCH', [
            'body' => [
                'name' => 'Test User'
            ]
        ]);

        $this->assertSame('ok', $response['status']);
        $this->assertSame('Test User', $response['data']['content']['name']);
    }

    public function testChangeEmail()
    {
        $app = $this->app;

        $response = $app->api()->call('users/admin@getkirby.com/email', 'PATCH', [
            'body' => [
                'email' => 'admin@getkirby.de'
            ]
        ]);

        $this->assertSame('ok', $response['status']);
        $this->assertSame('admin@getkirby.de', $response['data']['email']);
    }

    public function testChangeLanguage()
    {
        $app = $this->app;

        $response = $app->api()->call('users/admin@getkirby.com/language', 'PATCH', [
            'body' => [
                'language' => 'de'
            ]
        ]);

        $this->assertSame('ok', $response['status']);
        $this->assertSame('de', $response['data']['language']);
    }

    public function testChangePassword()
    {
        $app = $this->app;

        $response = $app->api()->call('users/admin@getkirby.com/password', 'PATCH', [
            'body' => [
                'password' => 'super-secure-new-password'
            ]
        ]);

        $this->assertSame('ok', $response['status']);
    }

    public function testChangeRole()
    {
        $app = $this->app;

        $response = $app->api()->call('users/editor@getkirby.com/role', 'PATCH', [
            'body' => [
                'role' => 'editor'
            ]
        ]);

        $this->assertSame('ok', $response['status']);
        $this->assertSame('editor', $response['data']['role']['name']);
    }

    public function testDelete()
    {
        $response = $this->app->api()->call('users/admin@getkirby.com', 'DELETE');
        $this->assertTrue($response);
    }

    public function testSearchWithGetRequest()
    {
        $app = $this->app;

        $response = $app->api()->call('users/search', 'GET', [
            'query' => [
                'q' => 'editor'
            ]
        ]);

        $this->assertCount(1, $response['data']);
        $this->assertEquals('editor@getkirby.com', $response['data'][0]['email']);
    }

    public function testSearchWithPostRequest()
    {
        $app = $this->app;

        $response = $app->api()->call('users/search', 'POST', [
            'body' => [
                'search' => 'editor'
            ]
        ]);

        $this->assertCount(1, $response['data']);
        $this->assertEquals('editor@getkirby.com', $response['data'][0]['email']);
    }

    public function testSearchName()
    {
        $app = $this->app;

        $response = $app->api()->call('users/search', 'GET', [
            'query' => [
                'q' => 'Bastian'
            ]
        ]);

        $this->assertCount(1, $response['data']);
        $this->assertEquals('admin@getkirby.com', $response['data'][0]['email']);
    }

    public function testFiles()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
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

        $app->impersonate('kirby');

        $response = $app->api()->call('users/test@getkirby.com/files');

        $this->assertCount(3, $response['data']);
        $this->assertSame('a.jpg', $response['data'][0]['filename']);
        $this->assertSame('b.jpg', $response['data'][1]['filename']);
        $this->assertSame('c.jpg', $response['data'][2]['filename']);
    }

    public function testFields()
    {
        $app = $this->app->clone([
            'blueprints' => [
                'users/admin' => [
                    'fields' => [
                        'test' => [
                            'type' => 'test'
                        ]
                    ]
                ]
            ],
            'fields' => [
                'test' => [
                    'api' => function () {
                        return [
                            [
                                'pattern' => '/',
                                'action'  => function () {
                                    return 'Test home route';
                                }
                            ],
                            [
                                'pattern' => 'nested',
                                'action'  => function () {
                                    return 'Test nested route';
                                }
                            ],
                        ];
                    }
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('users/admin@getkirby.com/fields/test');

        $this->assertSame('Test home route', $response);

        $response = $app->api()->call('users/admin@getkirby.com/fields/test/nested');

        $this->assertSame('Test nested route', $response);
    }

    public function testFilesSorted()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
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

        $app->impersonate('kirby');

        $response = $app->api()->call('users/test@getkirby.com/files');

        $this->assertEquals('b.jpg', $response['data'][0]['filename']);
        $this->assertEquals('a.jpg', $response['data'][1]['filename']);
    }

    public function testFile()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'files' => [
                        [
                            'filename' => 'a.jpg',
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('users/test@getkirby.com/files/a.jpg');

        $this->assertEquals('a.jpg', $response['data']['filename']);
    }

    public function testAvatar()
    {
        $this->app->impersonate('kirby');

        // create an avatar for the user
        $this->app->user('admin@getkirby.com')->createFile([
            'filename' => 'profile.jpg',
            'source'   => __DIR__ . '/fixtures/avatar.jpg',
            'template' => 'avatar',
        ]);

        $response = $this->app->api()->call('users/admin@getkirby.com/avatar');

        $this->assertEquals('profile.jpg', $response['data']['filename']);
    }

    public function testAvatarDelete()
    {
        $this->app->impersonate('kirby');

        // create an avatar for the user
        $this->app->user('admin@getkirby.com')->createFile([
            'filename' => 'profile.jpg',
            'source'   => __DIR__ . '/fixtures/avatar.jpg',
            'template' => 'avatar',
        ]);

        $response = $this->app->api()->call('users/admin@getkirby.com/avatar', 'DELETE');

        $this->assertTrue($response);
    }

    public function testSections()
    {
        $app = $this->app->clone([
            'blueprints' => [
                'users/admin' => [
                    'sections' => [
                        'test' => [
                            'type' => 'test'
                        ]
                    ]
                ]
            ],
            'sections' => [
                'test' => [
                    'toArray' => function () {
                        return [
                            'foo' => 'bar'
                        ];
                    }
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('users/admin@getkirby.com/sections/test');
        $expected = [
            'status' => 'ok',
            'code'   => 200,
            'name'   => 'test',
            'type'   => 'test',
            'foo'    => 'bar'
        ];

        $this->assertSame($expected, $response);
    }
}
