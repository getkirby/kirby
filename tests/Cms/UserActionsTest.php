<?php

namespace Kirby\Cms;

class UserActionsTest extends TestCase
{

    public function appProps(): array
    {
        return [
            'blueprints' => [
                'users/admin' => [
                    'name'   => 'admin',
                    'title'  => 'Admin',
                    'fields' => [
                        'website' => [
                            'type' => 'url'
                        ]
                    ]
                ]
            ],
            'roles' => [
                [
                    'title' => 'Admin',
                    'name'  => 'admin'
                ],
                [
                    'title' => 'Editor',
                    'name'  => 'editor'
                ]
            ],
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin'
                ],
                [
                    'email' => 'editor@getkirby.com',
                    'role'  => 'editor'
                ]
            ]
        ];
    }

    public function testChangeEmail()
    {
        $this->assertHooks([
            'user.changeEmail:before' => function (User $user, string $email) {
                $this->assertEquals('test@getkirby.com', $user->email());
                $this->assertEquals('another@getkirby.com', $email);
            },
            'user.changeEmail:after' => function (User $newUser, User $oldUser) {
                $this->assertEquals('another@getkirby.com', $newUser->email());
                $this->assertEquals('test@getkirby.com', $oldUser->email());
            }
        ], function ($app) {
            $result = $app->user()->changeEmail('another@getkirby.com');
            $this->assertEquals('another@getkirby.com', $result->email());
        }, $this->appProps());
    }

    public function testChangeName()
    {
        $this->assertHooks([
            'user.changeName:before' => function (User $user, string $name) {
                $this->assertEquals(null, $user->name());
                $this->assertEquals('Awesome User', $name);
            },
            'user.changeName:after' => function (User $newUser, User $oldUser) {
                $this->assertEquals(null, $oldUser->name());
                $this->assertEquals('Awesome User', $newUser->name());
            }
        ], function ($app) {
            $result = $app->user()->changeName('Awesome User');
            $this->assertEquals('Awesome User', $result->name());
        }, $this->appProps());
    }

    public function testChangePassword()
    {
        $this->assertHooks([
            'user.changePassword:before' => function (User $user) {
                $this->assertInstanceOf(User::class, $user);
            },
            'user.changePassword:after' => function (User $newUser, User $oldUser) {
                $this->assertNotEquals($newUser->password(), $oldUser->password());
            }
        ], function ($app) {
            $app->user()->changePassword('top-secret');
        }, $this->appProps());
    }

    public function testChangeRole()
    {
        $this->assertHooks([
            'user.changeRole:before' => function (User $user, string $role) {
                $this->assertEquals('editor', $user->role()->name());
                $this->assertEquals('admin', $role);
            },
            'user.changeRole:after' => function (User $newUser, User $oldUser) {
                $this->assertEquals('admin', $newUser->role()->name());
                $this->assertEquals('editor', $oldUser->role()->name());
            }
        ], function ($app) {
            $app->users()->find('editor@getkirby.com')->changeRole('admin');
        }, $this->appProps());
    }

    public function testUpdate()
    {
        $this->assertHooks([
            'user.update:before' => function (User $user, array $values, array $strings) {
                $this->assertEquals(null, $user->website()->value());
                $this->assertEquals('https://test.com', $strings['website']);
            },
            'user.update:after' => function (User $newUser, User $oldUser) {
                $this->assertEquals('https://test.com', $newUser->website()->value());
                $this->assertEquals(null, $oldUser->website()->value());
            }
        ], function ($app) {
            $app->user()->update([
                'website' => 'https://test.com',
            ]);
        }, $this->appProps());
    }


}
