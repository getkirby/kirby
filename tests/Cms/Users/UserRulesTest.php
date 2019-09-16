<?php

namespace Kirby\Cms;

use Exception;

class UserRulesTest extends TestCase
{
    public function appWithAdmin()
    {
        return new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures',
            ],
            'user' => 'admin@domain.com',
            'users' => [
                ['email' => 'user@domain.com', 'role' => 'editor'],
                ['email' => 'admin@domain.com', 'role' => 'admin']
            ]
        ]);
    }

    public function validDataProvider()
    {
        return [
            ['Email', 'editor@domain.com'],
            ['Language', 'en'],
            ['Password', '12345678'],
            ['Role', 'editor']
        ];
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testChangeValid($key, $value)
    {
        $kirby = $this->appWithAdmin();
        $user  = $kirby->user('user@domain.com');

        $this->assertTrue(UserRules::{'change' . $key}($user, $value));
    }

    public function invalidDataProvider()
    {
        return [
            ['Email', 'domain.com', 'Please enter a valid email address'],
            ['Language', 'english', 'Please enter a valid language'],
            ['Password', '1234', 'Please enter a valid password. Passwords must be at least 8 characters long.'],
            ['Role', 'rockstar', 'Please enter a valid role']
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testChangeInvalid($key, $value, $message)
    {
        $kirby = $this->appWithAdmin();
        $user  = $kirby->user('user@domain.com');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);

        $this->assertTrue(UserRules::{'change' . $key}($user, $value));
    }

    public function missingPermissionProvider()
    {
        return [
            ['Email', 'domain.com', 'You are not allowed to change the email for the user "test"'],
            ['Language', 'english', 'You are not allowed to change the language for the user "test"'],
            ['Password', '1234', 'You are not allowed to change the password for the user "test"'],
        ];
    }

    /**
     * @dataProvider missingPermissionProvider
     */
    public function testChangeWithoutPermission($key, $value, $message)
    {
        $permissions = $this->createMock(UserPermissions::class);
        $permissions->method('__call')->with('change' . $key)->willReturn(false);

        $user = $this->createMock(User::class);
        $user->method('permissions')->willReturn($permissions);
        $user->method('username')->willReturn('test');

        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage($message);

        UserRules::{'change' . $key}($user, $value);
    }

    public function testChangeEmailDuplicate()
    {
        $this->expectException('Kirby\Exception\DuplicateException');
        $this->expectExceptionCode('error.user.duplicate');

        $kirby = $this->appWithAdmin();

        UserRules::changeEmail($kirby->user('user@domain.com'), 'admin@domain.com');
    }

    public function testChangeRoleFromAdminByAdmin()
    {
        $kirby = new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures',
            ],
            'user' => 'admin@domain.com',
            'users' => [
                ['email' => 'user@domain.com', 'role' => 'admin'],
                ['email' => 'admin@domain.com', 'role' => 'admin']
            ]
        ]);
        $kirby->impersonate('admin@domain.com');

        $this->assertTrue(UserRules::changeRole($kirby->user('user@domain.com'), 'editor'));
    }

    public function testChangeRoleFromAdminByNonAdmin()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionCode('error.user.changeRole.permission');

        $kirby = new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures',
            ],
            'user' => 'user@domain.com',
            'users' => [
                ['email' => 'user@domain.com', 'role' => 'editor'],
                ['email' => 'admin@domain.com', 'role' => 'admin']
            ]
        ]);
        $kirby->impersonate('user@domain.com');

        UserRules::changeRole($kirby->user('admin@domain.com'), 'editor');
    }

    public function testChangeRoleToAdminByAdmin()
    {
        $kirby = new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures',
            ],
            'user' => 'user1@domain.com',
            'users' => [
                ['email' => 'user1@domain.com', 'role' => 'admin'],
                ['email' => 'user2@domain.com', 'role' => 'editor']
            ]
        ]);
        $kirby->impersonate('user1@domain.com');

        $this->assertTrue(UserRules::changeRole($kirby->user('user2@domain.com'), 'admin'));
    }

    public function testChangeRoleToAdminByNonAdmin()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionCode('error.user.changeRole.toAdmin');

        $kirby = new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures',
            ],
            'user' => 'user1@domain.com',
            'users' => [
                ['email' => 'user1@domain.com', 'role' => 'editor'],
                ['email' => 'user2@domain.com', 'role' => 'editor']
            ]
        ]);
        $kirby->impersonate('user1@domain.com');

        UserRules::changeRole($kirby->user('user2@domain.com'), 'admin');
    }

    public function testChangeRoleLastAdmin()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.user.changeRole.lastAdmin');

        $kirby = $this->appWithAdmin();
        $kirby->impersonate('kirby');

        UserRules::changeRole($kirby->user('admin@domain.com'), 'editor');
    }

    public function testCreate()
    {
        $user = new User($props = [
            'email'    => 'new-user@domain.com',
            'password' => '12345678',
            'language' => 'en',
            'kirby'    => $this->appWithAdmin()
        ]);

        $this->assertTrue(UserRules::create($user, $props));
    }

    public function testUpdate()
    {
        $app  = $this->appWithAdmin();
        $user = new User(['email' => 'user@domain.com']);
        $this->assertTrue(UserRules::update($user, $input = [
            'zodiac' => 'lion'
        ], $input));
    }

    public function testDelete()
    {
        $app  = $this->appWithAdmin();
        $user = new User(['email' => 'user@domain.com']);
        $this->assertTrue(UserRules::delete($user));
    }

    public function testDeleteLastAdmin()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.user.delete.lastAdmin');

        $kirby = $this->appWithAdmin();
        UserRules::delete($kirby->user('admin@domain.com'));
    }

    public function testDeleteLastUser()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.user.delete.lastAdmin');

        $kirby = $this->appWithAdmin();
        UserRules::delete($kirby->user('user@domain.com'));
        UserRules::delete($kirby->user('admin@domain.com'));
    }
}
