<?php

namespace Kirby\Cms;

class UserPropsTest extends TestCase
{

    public function testAvatar()
    {
        $user = new User([
            'id' => 'user@domain.com'
        ]);

        $user->set('avatar', $avatar = new Avatar([
            'root' => '/var/avatar.jpg',
            'url'  => '/users/avatar.jpg',
            'user' => $user
        ]));

        $this->assertEquals($avatar, $user->avatar());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "avatar" attribute must be of type "Kirby\Cms\Avatar"
     */
    public function testInvalidAvatar()
    {
        $user = new User(['id' => 'user@domain.com', 'avatar' => 'something']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "media" does not exist
     */
    public function testDefaultAvatarWithoutMediaManager()
    {
        $user = new User([
            'id' => 'user@domain.com'
        ]);

        $user->avatar();
    }

    public function testDefaultAvatarWithMediaManager()
    {
        $this->markTestIncomplete();
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
     * @expectedException Exception
     * @expectedExceptionMessage The "collection" attribute must be of type "Kirby\Cms\Users"
     */
    public function testInvalidCollection()
    {
        $user = new User(['id' => 'user@domain.com', 'collection' => 'something']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testDefaultCollectionWithoutStore()
    {
        $user = new User(['id' => 'user@domain.com']);
        $user->collection();
    }

    public function testDefaultCollectionWithStore()
    {
        $user = new User([
            'id'    => 'user@domain.com',
            'store' => new Store([
                'users' => function () {
                    return new Users();
                }
            ])
        ]);

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
     * @expectedException Exception
     * @expectedExceptionMessage The "content" attribute must be of type "Kirby\Cms\Content"
     */
    public function testInvalidContent()
    {
        $user = new User(['id' => 'user@domain.com', 'content' => 'something']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testDefaultContentWithoutStore()
    {
        $user = new User(['id' => 'user@domain.com']);
        $user->content();
    }

    public function testDefaultContentWithStore()
    {
        $user = new User([
            'id'    => 'user@domain.com',
            'store' => new Store([
                'user.content' => function ($user) {
                    return new Content(['name' => 'User'], $user);
                }
            ])
        ]);

        $this->assertInstanceOf(Content::class, $user->content());
        $this->assertInstanceOf(Field::class, $user->content()->get('name'));
        $this->assertEquals('User', $user->name()->value());
    }

    public function testId()
    {
        $user = new User([
            'id' => $id = 'user@domain.com',
        ]);

        $this->assertEquals($id, $user->id());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "id" attribute must be of type "string"
     */
    public function testInvalidId()
    {
        $user = new User(['id' => false]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "id" prop is missing
     */
    public function testEmptyId()
    {
        $user = new User();
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
     * @expectedException Exception
     * @expectedExceptionMessage The "root" attribute must be of type "string"
     */
    public function testInvalidRoot()
    {
        $user = new User(['id' => 'user@domain.com', 'root' => false]);
    }

    public function testStore()
    {
        $user = new User([
            'id'    => 'user@domain.com',
            'store' => $store = new Store()
        ]);

        $this->assertEquals($store, $user->store());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "store" attribute must be of type "Kirby\Cms\Store"
     */
    public function testInvalidStore()
    {
        $user = new User(['id' => 'user@domain.com', 'store' => 'something']);
    }

}
