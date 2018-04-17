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

    /**
     * @expectedException Kirby\Exception\DuplicateException
     * @expectedExceptionCode error.user.duplicate
     */
    public function testChangeEmailDuplicate()
    {
        $kirby = $this->appWithAdmin();

        UserRules::changeEmail($kirby->user('user@domain.com'), 'admin@domain.com');
    }

    /**
     * @expectedException Kirby\Exception\LogicException
     * @expectedExceptionCode error.user.changeRole.lastAdmin
     */
    public function testChangeRoleLastAdmin()
    {
        $kirby = $this->appWithAdmin();

        UserRules::changeRole($kirby->user('admin@domain.com'), 'editor');
    }

    public function testCreate()
    {
        $user = new User([
            'email'    => 'user@domain.com',
            'password' => '12345678',
            'kirby'    => $this->appWithAdmin()
        ]);

        $this->assertTrue(UserRules::create($user));
    }

    // /**
    //  * @expectedException Kirby\Exception\InvalidArgumentsException
    //  * @expectedExceptionMessage Please enter a valid email address
    //  */
    // public function testCreateInvalidPassword()
    // {
    //     $user = new User([
    //         'email' => 'user@domain.org',
    //         'password' => '12'
    //     ]);
    //     $form = Form::for($user);
    //     UserRules::create($user, $form);
    // }

    public function testUpdate()
    {
        $app  = $this->appWithAdmin();
        $user = new User(['email' => 'user@domain.com']);
        $this->assertTrue(UserRules::update($user, $input = [
            'zodiac' => 'lion'
        ], $input));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Use the User::changeEmail() method to change the user email
     */
    public function testUpdateWithEmail()
    {
        $app  = $this->appWithAdmin();
        $user = new User(['email' => 'user@domain.com']);
        $this->assertTrue(UserRules::update($user, $input = [
            'email' => 'admin@domain.com'
        ], $input));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Use the User::changePassword() method to change the user password
     */
    public function testUpdateWithPassword()
    {
        $app  = $this->appWithAdmin();
        $user = new User(['email' => 'user@domain.com']);
        $this->assertTrue(UserRules::update($user, $input = [
            'password' => '12345678'
        ], $input));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Use the User::changeRole() method to change the user role
     */
    public function testUpdateWithRole()
    {
        $app  = $this->appWithAdmin();
        $user = new User(['email' => 'user@domain.com']);
        $this->assertTrue(UserRules::update($user, $input = [
            'role' => 'editor'
        ], $input));
    }

    public function testDelete()
    {
        $app  = $this->appWithAdmin();
        $user = new User(['email' => 'user@domain.com']);
        $this->assertTrue(UserRules::delete($user));
    }

    /**
     * @expectedException Kirby\Exception\LogicException
     * @expectedExceptionCode error.user.delete.lastAdmin
     */
    public function testDeleteLastAdmin()
    {
        $kirby = $this->appWithAdmin();
        UserRules::delete($kirby->user('admin@domain.com'));
    }

    /**
     * @expectedException Kirby\Exception\LogicException
     * @expectedExceptionCode error.user.delete.lastUser
     */
    public function testDeleteLastUser()
    {
        $this->markTestIncomplete();
        // TODO: this will always trigger error.user.delete.lastAdmin

        $kirby = $this->appWithAdmin();
        UserRules::delete($kirby->user('user@domain.com'));
        UserRules::delete($kirby->user('admin@domain.com'));
    }

}
