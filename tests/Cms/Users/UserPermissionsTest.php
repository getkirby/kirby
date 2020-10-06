<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class UserPermissionsTest extends TestCase
{
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

    public function testChangeSingleRole()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'roles' => [
                ['name' => 'admin']
            ]
        ]);

        $user  = new User(['email' => 'test@getkirby.com']);
        $perms = $user->permissions();

        $this->assertFalse($perms->can('changeRole'));
    }
}
