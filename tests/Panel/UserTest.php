<?php

namespace Kirby\Panel;

use Kirby\Cms\User as ModelUser;
use PHPUnit\Framework\TestCase;

class ModelUserTestForceLocked extends ModelUser
{
    public function isLocked(): bool
    {
        return true;
    }
}

class UserTest extends TestCase
{
    public function testOptions()
    {
        $user = new ModelUser([
            'email' => 'test@getkirby.com',
        ]);

        $user->kirby()->impersonate('kirby');

        $expected = [
            'create'         => true,
            'changeEmail'    => true,
            'changeLanguage' => true,
            'changeName'     => true,
            'changePassword' => true,
            'changeRole'     => false, // just one role
            'delete'         => true,
            'update'         => true,
        ];

        $panel = new User($user);
        $this->assertEquals($expected, $panel->options());
    }

    public function testOptionsWithLockedUser()
    {
        $user = new ModelUserTestForceLocked([
            'email' => 'test@getkirby.com',
        ]);

        $user->kirby()->impersonate('kirby');

        // without override
        $expected = [
            'create'         => false,
            'changeEmail'    => false,
            'changeLanguage' => false,
            'changeName'     => false,
            'changePassword' => false,
            'changeRole'     => false,
            'delete'         => false,
            'update'         => false,
        ];

        $panel = new User($user);
        $this->assertEquals($expected, $panel->options());

        // with override
        $expected = [
            'create'         => false,
            'changeEmail'    => true,
            'changeLanguage' => false,
            'changeName'     => false,
            'changePassword' => false,
            'changeRole'     => false,
            'delete'         => false,
            'update'         => false,
        ];

        $this->assertEquals($expected, $panel->options(['changeEmail']));
    }
}
