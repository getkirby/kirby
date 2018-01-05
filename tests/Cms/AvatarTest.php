<?php

namespace Kirby\Cms;

use Kirby\Image\Image;

class AvatarTest extends TestCase
{

    public function avatar(array $props = [])
    {
        return new Avatar(array_merge([
            'url'  => '/media/users/example.jpg',
            'root' => '/var/www/media/users/example.jpg',
            'user' => $this->user()
        ], $props));
    }

    public function user()
    {
        return new User([
            'id' => 'mail@example.com'
        ]);
    }

    public function testAsset()
    {
        $avatar = $this->avatar([
            'asset' => $asset = new Image('/test.jpg', '/test.jpg')
        ]);

        $this->assertEquals($asset, $avatar->asset());
    }

    public function testClone()
    {
        $avatar = $this->avatar();
        $clone  = $avatar->clone();

        $this->assertEquals($avatar->root(), $clone->root());
        $this->assertEquals($avatar->url(), $clone->url());
        $this->assertEquals($avatar->user(), $clone->user());
    }

    public function testCreate()
    {
        $this->markTestIncomplete();
    }

    public function testDelete()
    {
        $this->markTestIncomplete();
    }

    public function testReplace()
    {
        $this->markTestIncomplete();
    }

    public function testRoot()
    {
        $avatar = $this->avatar([
            'root' => $root = '/var/users/example.jpg'
        ]);

        $this->assertEquals($root, $avatar->root());
    }

    public function testUrl()
    {
        $avatar = $this->avatar([
            'url' => $url = 'https://cdn.example.com/users/example.jpg'
        ]);

        $this->assertEquals($url, $avatar->url());
    }

    public function testUser()
    {
        $avatar = $this->avatar([
            'user' => $user = new User(['id' => 'abc'])
        ]);

        $this->assertEquals($user, $avatar->user());
    }

}
