<?php

namespace Kirby\Cms;

class UserPropsTest extends TestCase
{

    public function testAvatar()
    {
        $user = new User([
            'email' => 'user@domain.com'
        ]);

        $this->assertInstanceOf(Avatar::class, $user->avatar());
    }

    public function testCollection()
    {
        $user = new User([
            'email'      => 'user@domain.com',
            'collection' => $users = new Users()
        ]);

        $this->assertEquals($users, $user->collection());
    }

    /**
     * @expectedException TypeError
     */
    public function testInvalidCollection()
    {
        $user = new User(['email' => 'user@domain.com', 'collection' => 'something']);
    }

    public function testDefaultCollection()
    {
        $user = new User(['email' => 'user@domain.com']);
        $this->assertInstanceOf(Users::class, $user->collection());
    }

    public function testContent()
    {
        $user = new User([
            'email'   => 'user@domain.com',
            'content' => $content = ['name' => 'Test']
        ]);

        $this->assertEquals($content, $user->content()->toArray());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\User::setContent() must be of the type array or null, string given
     */
    public function testInvalidContent()
    {
        $user = new User(['email' => 'user@domain.com', 'content' => 'something']);
    }

    public function testDefaultContent()
    {
        $user = new User(['email' => 'user@domain.com']);
        $this->assertInstanceOf(Content::class, $user->content());
    }

    public function testEmail()
    {
        $user = new User([
            'email' => $email = 'user@domain.com',
        ]);

        $this->assertEquals($email, $user->email());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\User::setEmail() must be of the type string, array given
     */
    public function testInvalidEmail()
    {
        $user = new User(['email' => []]);
    }

}
