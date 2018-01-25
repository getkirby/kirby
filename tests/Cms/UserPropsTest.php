<?php

namespace Kirby\Cms;

class UserPropsTest extends TestCase
{

    public function testDefaultAvatar()
    {
        $user = new User([
            'id' => 'user@domain.com'
        ]);

        $this->assertInstanceOf(Avatar::class, $user->avatar());
    }

    public function testCustomAvatar()
    {
        $user = new User([
            'id'     => 'user@domain.com',
            'avatar' => $avatar = new Avatar([
                'url'  => '/users/something.jpg',
                'root' => '/users/something.jpg'
            ])
        ]);

        $this->assertInstanceOf(Avatar::class, $user->avatar());
        $this->assertEquals($avatar->url(), $user->avatar()->url());
    }

    public function testCollection()
    {
        $user = new User([
            'id'         => 'user@domain.com',
            'collection' => $users = new Users()
        ]);

        $this->assertEquals($users, $user->collection());
    }

    /**
     * @expectedException TypeError
     */
    public function testInvalidCollection()
    {
        $user = new User(['id' => 'user@domain.com', 'collection' => 'something']);
    }

    public function testDefaultCollection()
    {
        $user = new User(['id' => 'user@domain.com']);
        $this->assertInstanceOf(Users::class, $user->collection());
    }

    public function testContent()
    {
        $user = new User([
            'id'      => 'user@domain.com',
            'content' => $content = new Content()
        ]);

        $this->assertEquals($content, $user->content());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\User::setContent() must be an instance of Kirby\Cms\Content or null, string given
     */
    public function testInvalidContent()
    {
        $user = new User(['id' => 'user@domain.com', 'content' => 'something']);
    }

    public function testDefaultContent()
    {
        $user = new User(['id' => 'user@domain.com']);
        $this->assertInstanceOf(Content::class, $user->content());
    }

    public function testId()
    {
        $user = new User([
            'id' => $id = 'user@domain.com',
        ]);

        $this->assertEquals($id, $user->id());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\User::setId() must be of the type string, array given
     */
    public function testInvalidId()
    {
        $user = new User(['id' => []]);
    }

    public function testRoot()
    {
        $user = new User([
            'id'   => 'user@domain.com',
            'root' => $root = '/var/accounts/user@domain.com'
        ]);

        $this->assertEquals($root, $user->root());
    }

    /**
     * @expectedException TypeError
     */
    public function testInvalidRoot()
    {
        $user = new User(['id' => 'user@domain.com', 'root' => []]);
    }

}
