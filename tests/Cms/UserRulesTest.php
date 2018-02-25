<?php

namespace Kirby\Cms;

class UserRulesTest extends TestCase
{

    public function userProvider()
    {
        return [
            ['Email', 'admin@domain.com'],
            ['Language', 'en_US'],
            ['Password', '12345678'],
            ['Role', 'editor']
        ];
    }

    /**
     * @dataProvider userProvider
     */
    public function testChange($key, $value)
    {
        $user = new User(['email' => 'user@domain.com']);
        $this->assertTrue(UserRules::{'change' . $key}($user, $value));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Please enter a valid email address
     */
    public function testChangeEmailInvalid()
    {
        $user = new User(['email' => 'user@domain.com']);
        UserRules::changeEmail($user, 'getkirby.com');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage A user with this email address already exists
     */
    public function testChangeEmailDuplicate()
    {
        $kirby = new App([
            'components' => [
                'users' => new Users([
                    new User(['email' => 'user@domain.com']),
                    new User(['email' => 'admin@domain.com'])
                ])
            ]
        ]);

        UserRules::changeEmail($kirby->users()->first(), 'admin@domain.com');
    }

    /**
     *
     * @expectedException Exception
     * @expectedExceptionMessage Invalid user language
     */
    public function testChangeLanguageInvalid()
    {
        $user = new User(['email' => 'user@domain.com']);
        UserRules::changeLanguage($user, 'english');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The password must be at least 8 characters long
     */
    public function testChangePasswordInvalid()
    {
        $user = new User(['email' => 'user@domain.com']);
        UserRules::changePassword($user, '1234');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid user role: "rockstar"
     */
    public function testChangeRoleInvalid()
    {
        $user = new User(['email' => 'user@domain.com']);
        UserRules::changeRole($user, 'rockstar');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The role for the last admin cannot be changed
     */
    public function testChangeRoleLastAdmin()
    {
        $kirby = new App([
            'components' => [
                'users' => new Users([
                    new User(['email' => 'user@domain.com', 'role' => 'admin']),
                    new User(['email' => 'admin@domain.com', 'role' => 'editor'])
                ])
            ]
        ]);

        UserRules::changeRole($kirby->users()->first(), 'editor');
    }

    public function testCreate()
    {
        $user = new User($values = [
            'email'    => 'user@domain.com',
            'password' => '12345678'
        ]);
        $form = Form::for($user);
        $this->assertTrue(UserRules::create($user, $values, $form));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The property "email" is required
     */
    public function testCreateWithoutEmail()
    {
        $user = new User(['password' => '123']);
        $form = Form::for($user);
        UserRules::create($user, $form);
    }


    // /**
    //  * @expectedException Exception
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
        $user = new User(['email' => 'user@domain.com']);
        $form = Form::for($user);
        $this->assertTrue(UserRules::update($user, [
            'zodiac' => 'lion'
        ], $form));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Use the User::changeEmail() method to change the user email
     */
    public function testUpdateWithEmail()
    {
        $user = new User(['email' => 'user@domain.com']);
        $form = Form::for($user);
        $this->assertTrue(UserRules::update($user, [
            'email' => 'admin@domain.com'
        ], $form));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Use the User::changePassword() method to change the user password
     */
    public function testUpdateWithPassword()
    {
        $user = new User(['email' => 'user@domain.com']);
        $form = Form::for($user);
        $this->assertTrue(UserRules::update($user, [
            'password' => '12345678'
        ], $form));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Use the User::changeRole() method to change the user role
     */
    public function testUpdateWithRole()
    {
        $user = new User(['email' => 'user@domain.com']);
        $form = Form::for($user);
        $this->assertTrue(UserRules::update($user, [
            'role' => 'editor'
        ], $form));
    }

    public function testDelete()
    {
        $user = new User(['email' => 'user@domain.com']);
        $this->assertTrue(UserRules::delete($user));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The last admin cannot be deleted
     */
    public function testDeleteLastAdmin()
    {
        $kirby = new App([
            'components' => [
                'users' => new Users([
                    new User(['email' => 'user@domain.com', 'role' => 'admin']),
                    new User(['email' => 'admin@domain.com', 'role' => 'editor'])
                ])
            ]
        ]);

        UserRules::delete($kirby->users()->first());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The last user cannot be deleted
     */
    public function testDeleteLastUser()
    {
        $kirby = new App([
            'components' => [
                'users' => new Users([
                    new User(['email' => 'user@domain.com', 'role' => 'editor']),
                ])
            ]
        ]);

        UserRules::delete($kirby->users()->first());
    }

}
