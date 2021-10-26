<?php

namespace Kirby\Cms;

/**
 * @coversDefaultClass \Kirby\Cms\User
 */
class UserMethodsTest extends TestCase
{
    public function setUp(): void
    {
        // make sure field methods are loaded
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    /**
     * @covers ::id
     */
    public function testId()
    {
        $user = new User([
            'id'    => 'test',
            'email' => 'user@domain.com'
        ]);
        $this->assertEquals('test', $user->id());
    }

    /**
     * @covers ::language
     */
    public function testLanguage()
    {
        $user = new User([
            'email'    => 'user@domain.com',
            'language' => 'en',
        ]);

        $this->assertEquals('en', $user->language());
    }

    /**
     * @covers ::language
     */
    public function testLanguageDefault()
    {
        $user = new User([
            'email' => 'user@domain.com',
        ]);

        $this->assertEquals('en', $user->language());
    }

    /**
     * @covers ::role
     */
    public function testRole()
    {
        $this->app = $this->app->clone([
            'roles' => [
                ['name' => 'editor', 'title' => 'Editor']
            ]
        ]);

        $user = new User([
            'email' => 'user@domain.com',
            'role'  => 'editor',
            'kirby' => $this->app
        ]);

        $this->assertEquals('editor', $user->role()->name());
    }

    /**
     * @covers ::role
     */
    public function testRoleDefault()
    {
        $user = new User([
            'email' => 'user@domain.com',
        ]);

        $this->assertEquals('nobody', $user->role()->name());
    }

    /**
     * When there's no authenticated user
     * the roles collection must only contain
     * the already assigned role of the user
     *
     * @covers ::roles
     */
    public function testRolesWithoutAuthenticatedUser()
    {
        $this->app = $this->app->clone([
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'editor'],
            ]
        ]);

        $user = new User([
            'email' => 'user@domain.com',
            'role'  => 'editor'
        ]);

        $this->assertCount(1, $user->roles());
        $this->assertSame('editor', $user->roles()->first()->id());
    }

    /**
     * When there are two admins or more,
     * one admin can assign any other role to the
     * other admin
     *
     * @covers ::roles
     */
    public function testRolesForAdmin()
    {
        $this->app = $this->app->clone([
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'editor'],
            ],
            'users' => [
                ['email' => 'admin1@getkirby.com', 'role' => 'admin'],
                ['email' => 'admin2@getkirby.com', 'role' => 'admin'],
                ['email' => 'editor@getkirby.com', 'role' => 'editor'],
            ]
        ]);

        $this->app->impersonate('admin2@getkirby.com');

        $admin1 = $this->app->user('admin1@getkirby.com');

        $this->assertCount(2, $admin1->roles());
        $this->assertSame('admin', $admin1->roles()->first()->id());
        $this->assertSame('editor', $admin1->roles()->last()->id());

        $editor = $this->app->user('admin1@getkirby.com');

        $this->assertCount(2, $editor->roles());
        $this->assertSame('admin', $editor->roles()->first()->id());
        $this->assertSame('editor', $editor->roles()->last()->id());
    }

    /**
     * @covers ::roles
     */
    public function testRolesForEditorWithDefaultPermissions()
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
                ['email' => 'client@getkirby.com', 'role' => 'client'],
            ]
        ]);

        $this->app->impersonate('editor@getkirby.com');

        $admin = $this->app->user('admin@getkirby.com');

        $this->assertCount(1, $admin->roles());
        $this->assertSame('admin', $admin->roles()->first()->id());

        $client = $this->app->user('client@getkirby.com');

        $this->assertCount(2, $client->roles());
        $this->assertSame('client', $client->roles()->first()->id());
        $this->assertSame('editor', $client->roles()->last()->id());
    }

    /**
     * When the user does not have permissions to change the role
     * of any user, they should only get back a collection with
     * their own role
     *
     * @covers ::roles
     */
    public function testRolesForEditorWithBlockedPermissions()
    {
        $this->app = $this->app->clone([
            'roles' => [
                [
                    'name' => 'admin'
                ],
                [
                    'name' => 'editor',
                    'permissions' => [
                        'users' => [
                            'changeRole' => false
                        ]
                    ]
                ],
                [
                    'name' => 'client'
                ],
            ],
            'users' => [
                ['email' => 'admin@getkirby.com', 'role' => 'admin'],
                ['email' => 'editor@getkirby.com', 'role' => 'editor'],
                ['email' => 'client@getkirby.com', 'role' => 'client'],
            ]
        ]);

        $this->app->impersonate('editor@getkirby.com');

        $admin = $this->app->user('admin@getkirby.com');

        $this->assertCount(1, $admin->roles());
        $this->assertSame('admin', $admin->roles()->first()->id());

        $client = $this->app->user('client@getkirby.com');

        $this->assertCount(1, $client->roles());
        $this->assertSame('client', $client->roles()->first()->id());
    }

    /**
     * Change role permissions are blocked on the global blueprint
     * level but then again allowed for the individual role
     *
     * @covers ::roles
     */
    public function testRolesForEditorWithExplicitPermissions()
    {
        $this->app = $this->app->clone([
            'blueprints' => [
                'users/client' => [
                    'options' => [
                        'changeRole' => [
                            '*'      => false,
                            'editor' => true
                        ]
                    ]
                ],
            ],
            'roles' => [
                [
                    'name' => 'admin'
                ],
                [
                    'name' => 'editor',
                    'permissions' => [
                        'users' => [
                            'changeRole' => false
                        ]
                    ]
                ],
                [
                    'name' => 'client'
                ],
            ],
            'users' => [
                ['email' => 'admin@getkirby.com', 'role' => 'admin'],
                ['email' => 'editor@getkirby.com', 'role' => 'editor'],
                ['email' => 'client@getkirby.com', 'role' => 'client'],
            ]
        ]);

        $this->app->impersonate('editor@getkirby.com');

        $client = $this->app->user('client@getkirby.com');

        $this->assertCount(2, $client->roles());
        $this->assertSame('client', $client->roles()->first()->id());
        $this->assertSame('editor', $client->roles()->last()->id());
    }

    /**
     * When only one admin is left, the role
     * of that admin cannot be changed anymore
     *
     * @covers ::roles
     */
    public function testRolesForLastAdmin()
    {
        $this->app = $this->app->clone([
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'editor'],
            ]
        ]);

        $user = new User([
            'email' => 'user@domain.com',
            'role'  => 'admin'
        ]);

        $this->assertCount(1, $user->roles());
        $this->assertSame('admin', $user->roles()->first()->id());
    }

    /**
     * When the user has the nobody role,
     * they cannot be promoted to the admin role
     *
     * @covers ::roles
     */
    public function testRolesWithoutOptions()
    {
        $user = new User([
            'email' => 'user@domain.com',
        ]);

        $this->assertCount(0, $user->roles());
    }
}
