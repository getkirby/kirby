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
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $user  = new User(['email' => 'test@getkirby.com']);
        $perms = $user->permissions();

        $this->assertFalse($perms->can($action));
    }
}
