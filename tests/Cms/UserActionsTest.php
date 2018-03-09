<?php

namespace Kirby\Cms;

class UserActionsTest extends TestCase
{

    public function setUp()
    {
        App::removePlugins();
    }

    public function userDummy()
    {
        $user = new User([
            'email' => 'test@test.com',
        ]);

        return $user->clone([
            'blueprint' => new UserBlueprint([
                'model' => $user,
                'name'  => 'Test User',
                'title' => 'user'
            ])
        ]);
    }

    public function testChangeEmail()
    {
        $this->assertHooks([
            'user.changeEmail:before' => function (User $user, string $email) {
                $this->assertEquals('test@test.com', $user->email());
                $this->assertEquals('another@email.com', $email);
            },
            'user.changeEmail:after' => function (User $newUser, User $oldUser) {
                $this->assertEquals('another@email.com', $newUser->email());
                $this->assertEquals('test@test.com', $oldUser->email());
            }
        ], function () {
            $result = $this->userDummy()->changeEmail('another@email.com');
            $this->assertEquals('another@email.com', $result->email());
        });
    }

    public function testChangeName()
    {
        $this->assertHooks([
            'user.changeName:before' => function (User $user, string $name) {
                $this->assertEquals('test@test.com', $user->name());
                $this->assertEquals('Awesome User', $name);
            },
            'user.changeName:after' => function (User $newUser, User $oldUser) {
                $this->assertEquals('test@test.com', $oldUser->name());
                $this->assertEquals('Awesome User', $newUser->name());
            }
        ], function () {
            $result = $this->userDummy()->changeName('Awesome User');
            $this->assertEquals('Awesome User', $result->name());
        });
    }

}
