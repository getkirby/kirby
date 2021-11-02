<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Form\Field;
use PHPUnit\Framework\TestCase;

class AccountRoutesTest extends TestCase
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
                'index' => $this->fixtures = __DIR__ . '/fixtures/AccountRoutesTest'
            ],
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin'
                ],
                [
                    'email' => 'editor@getkirby.com',
                    'role'  => 'admin'
                ]
            ]
        ]);

        $this->app->impersonate('test@getkirby.com');
    }

    public function tearDown(): void
    {
        App::destroy();
        Field::$types = [];
        Section::$types = [];
        Dir::remove($this->fixtures);
    }

    public function testAvatar()
    {
        // create an avatar for the user
        $this->app->user()->createFile([
            'filename' => 'profile.jpg',
            'source'   => __DIR__ . '/fixtures/avatar.jpg',
            'template' => 'avatar',
        ]);

        $response = $this->app->api()->call('account/avatar');

        $this->assertEquals('profile.jpg', $response['data']['filename']);
    }

    public function testAvatarDelete()
    {
        // create an avatar for the user
        $this->app->user()->createFile([
            'filename' => 'profile.jpg',
            'source'   => __DIR__ . '/fixtures/avatar.jpg',
            'template' => 'avatar',
        ]);

        $response = $this->app->api()->call('account/avatar', 'DELETE');

        $this->assertTrue($response);
    }

    public function testBlueprint()
    {
        $app = $this->app->clone([
            'blueprints' => [
                'users/admin' => [
                    'name'  => 'admin',
                    'title' => 'Test'
                ]
            ]
        ]);

        $app->impersonate('test@getkirby.com');

        $response = $app->api()->call('account/blueprint');

        $this->assertSame(200, $response['code']);
        $this->assertSame('Test', $response['data']['title']);
        $this->assertSame('admin', $response['data']['name']);
    }

    public function testBlueprints()
    {
        $app = $this->app->clone([
            'blueprints' => [
                'users/admin' => [
                    'name'     => 'admin',
                    'title'    => 'Admin',
                    'sections' => [
                        'test' => [
                            'type'   => 'pages',
                            'parent' => 'site',
                            'templates' => [
                                'foo',
                                'bar'
                            ]
                        ]
                    ]
                ],
                'users/editor' => [
                    'name'  => 'editor',
                    'title' => 'Editor'
                ]
            ]
        ]);

        $app->impersonate('test@getkirby.com');

        $response = $app->api()->call('account/blueprints');

        $this->assertCount(2, $response);
        $this->assertSame('Foo', $response[0]['title']);
        $this->assertSame('Bar', $response[1]['title']);
    }

    public function testChangeEmail()
    {
        $response = $this->app->api()->call('account/email', 'PATCH', [
            'body' => [
                'email' => 'admin@getkirby.de'
            ]
        ]);

        $this->assertSame('ok', $response['status']);
        $this->assertSame('admin@getkirby.de', $response['data']['email']);
    }

    public function testChangeLanguage()
    {
        $response = $this->app->api()->call('account/language', 'PATCH', [
            'body' => [
                'language' => 'de'
            ]
        ]);

        $this->assertSame('ok', $response['status']);
        $this->assertSame('de', $response['data']['language']);
    }

    public function testChangeName()
    {
        $response = $this->app->api()->call('account/name', 'PATCH', [
            'body' => [
                'name' => 'Test user'
            ]
        ]);

        $this->assertSame('ok', $response['status']);
        $this->assertSame('Test user', $response['data']['name']);
    }

    public function testChangePassword()
    {
        $response = $this->app->api()->call('account/password', 'PATCH', [
            'body' => [
                'password' => 'super-secure-new-password'
            ]
        ]);

        $this->assertSame('ok', $response['status']);
    }

    public function testChangeRole()
    {
        $response = $this->app->api()->call('account/role', 'PATCH', [
            'body' => [
                'role' => 'editor'
            ]
        ]);

        $this->assertSame('ok', $response['status']);
        $this->assertSame('editor', $response['data']['role']['name']);
    }

    public function testDelete()
    {
        $response = $this->app->api()->call('account', 'DELETE');
        $this->assertTrue($response);
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

        $app->impersonate('test@getkirby.com');

        $response = $app->api()->call('account/fields/test');

        $this->assertSame('Test home route', $response);

        $response = $app->api()->call('account/fields/test/nested');

        $this->assertSame('Test nested route', $response);
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

    public function testGet()
    {
        $app = $this->app;

        $response = $app->api()->call('account');

        $this->assertEquals('test@getkirby.com', $response['data']['email']);
    }

    public function testRoles()
    {
        $response = $this->app->api()->call('account/roles');

        $this->assertSame(200, $response['code']);

        $this->assertCount(2, $response['data']);
        $this->assertSame('admin', $response['data'][0]['name']);
        $this->assertSame('editor', $response['data'][1]['name']);
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

        $app->impersonate('test@getkirby.com');

        $response = $app->api()->call('account/sections/test');
        $expected = [
            'status' => 'ok',
            'code'   => 200,
            'name'   => 'test',
            'type'   => 'test',
            'foo'    => 'bar'
        ];

        $this->assertSame($expected, $response);
    }

    public function testUpdate()
    {
        $response = $this->app->api()->call('account', 'PATCH', [
            'body' => [
                'name' => 'Test User'
            ]
        ]);

        $this->assertSame('ok', $response['status']);
        $this->assertSame('Test User', $response['data']['content']['name']);
    }
}
