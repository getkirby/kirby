<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class UserPermissionsTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function actionProvider()
    {
        return [
            ['create'],
            ['changeEmail'],
            ['changeLanguage'],
            ['changeName'],
            ['changePassword'],
            ['changeRole'],
            ['delete'],
            ['update'],
        ];
    }

    /**
     * @dataProvider actionProvider
     */
    public function testWithAdmin($action)
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'editor']
            ]
        ]);

        $kirby->impersonate('kirby');

        $user  = new User(['email' => 'test@getkirby.com']);
        $perms = $user->permissions();

        $this->assertTrue($perms->can($action));
    }

    /**
     * @dataProvider actionProvider
     */
    public function testWithNobody($action)
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'editor']
            ]
        ]);

        $user  = new User(['email' => 'test@getkirby.com']);
        $perms = $user->permissions();

        $this->assertFalse($perms->can($action));
    }

    /**
     * @dataProvider actionProvider
     */
    public function testWithNoAdmin($action)
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'roles' => [
                ['name' => 'admin'],
                [
                    'name' => 'editor',
                    'permissions' => [
                        'user' => [
                            'changeEmail'    => false,
                            'changeLanguage' => false,
                            'changeName'     => false,
                            'changePassword' => false,
                            'changeRole'     => false,
                            'delete'         => false,
                            'update'         => false
                        ],
                        'users' => [
                            'changeEmail'    => false,
                            'changeLanguage' => false,
                            'changeName'     => false,
                            'changePassword' => false,
                            'changeRole'     => false,
                            'create'         => false,
                            'delete'         => false,
                            'update'         => false
                        ]
                    ]
                ]
            ],
            'user'  => 'editor@getkirby.com',
            'users' => [
                [
                    'email' => 'admin@getkirby.com',
                    'role'  => 'admin'
                ],
                [
                    'email' => 'editor@getkirby.com',
                    'role'  => 'editor'
                ]
            ],
        ]);

        $user  = $app->user();
        $perms = $user->permissions();

        $this->assertSame('editor', $user->role()->name());
        $this->assertFalse($perms->can($action));
    }

    public function testChangeRole()
    {
        $this->app = $this->app->clone([
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'editor'],
            ],
            'users' => [
                ['email' => 'admin@getkirby.com', 'role' => 'admin'],
                ['email' => 'editor@getkirby.com', 'role' => 'editor']
            ]
        ]);

        // authenticate the admin
        $this->app->impersonate('admin@getkirby.com');

        $editor = $this->app->user('editor@getkirby.com');
        $this->assertTrue($editor->permissions()->changeRole());
    }

    public function testChangeRoleOfLastAdmin()
    {
        $this->app = $this->app->clone([
            'users' => [
                ['email' => 'admin@getkirby.com', 'role' => 'admin'],
            ]
        ]);

        $admin = $this->app->impersonate('admin@getkirby.com');
        $this->assertFalse($admin->permissions()->changeRole());
    }

    public function testChangeRoleOfAdminWithoutAlternativeRole()
    {
        $this->app = $this->app->clone([
            'users' => [
                ['email' => 'admin1@getkirby.com', 'role' => 'admin'],
                ['email' => 'admin2@getkirby.com', 'role' => 'admin'],
            ]
        ]);

        $this->app->impersonate('admin1@getkirby.com');
        $admin2 = $this->app->user('admin2@getkirby.com');
        $this->assertFalse($admin2->permissions()->changeRole());
    }

    public function testChangeRoleOfAdminsAsEditor()
    {
        $this->app = $this->app->clone([
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'editor'],
                ['name' => 'client'],
            ],
            'users' => [
                ['email' => 'admin1@getkirby.com', 'role' => 'admin'],
                ['email' => 'admin2@getkirby.com', 'role' => 'admin'],
                ['email' => 'editor@getkirby.com', 'role' => 'editor']
            ]
        ]);

        // authenticate the editor
        $this->app->impersonate('editor@getkirby.com');

        // collect the admins
        $admin1 = $this->app->user('admin1@getkirby.com');
        $admin2 = $this->app->user('admin2@getkirby.com');

        $this->assertFalse($admin1->permissions()->changeRole());
        $this->assertFalse($admin2->permissions()->changeRole());
    }

    public function testChangeRoleOfClientAsEditor()
    {
        $this->app = $this->app->clone([
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'editor'],
                ['name' => 'client'],
            ],
            'users' => [
                ['email' => 'admin@getkirby.com', 'role' => 'admin'],
                ['email' => 'editor@getkirby.com', 'role' => 'editor'],
                ['email' => 'client@getkirby.com', 'role' => 'client']
            ]
        ]);

        // authenticate the editor
        $this->app->impersonate('editor@getkirby.com');

        // get the client
        $client = $this->app->user('client@getkirby.com');

        $this->assertTrue($client->permissions()->changeRole());
    }

    public function testChangeRoleOfClientAsEditorWithBlockedPermissions()
    {
        $this->app = $this->app->clone([
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'editor', 'permissions' => [
                    'users' => [
                        'changeRole' => false
                    ]
                ]],
                ['name' => 'client'],
            ],
            'users' => [
                ['email' => 'admin@getkirby.com', 'role' => 'admin'],
                ['email' => 'editor@getkirby.com', 'role' => 'editor'],
                ['email' => 'client@getkirby.com', 'role' => 'client']
            ]
        ]);

        // authenticate the editor
        $this->app->impersonate('editor@getkirby.com');

        // get the client
        $client = $this->app->user('client@getkirby.com');

        $this->assertFalse($client->permissions()->changeRole());
    }

    public function testChangeRoleOfClientAsEditorWithExplicitPermissions()
    {
        $this->app = $this->app->clone([
            'blueprints' => [
                'users/client' => [
                    'options' => [
                        'changeRole' => [
                            '*' => false,
                            'editor' => true
                        ]
                    ]
                ]
            ],
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'editor', 'permissions' => [
                    'users' => [
                        'changeRole' => false
                    ]
                ]],
                ['name' => 'client'],
            ],
            'users' => [
                ['email' => 'admin@getkirby.com', 'role' => 'admin'],
                ['email' => 'editor@getkirby.com', 'role' => 'editor'],
                ['email' => 'client@getkirby.com', 'role' => 'client']
            ]
        ]);

        // authenticate the editor
        $this->app->impersonate('editor@getkirby.com');

        // get the client
        $client = $this->app->user('client@getkirby.com');

        $this->assertTrue($client->permissions()->changeRole());
    }
}
