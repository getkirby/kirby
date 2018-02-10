<?php

namespace Kirby\Cms;

use Kirby\Image\Image;

class AvatarTest extends TestCase
{

    public function avatar(array $props = [])
    {
        return new Avatar(array_merge([
            'user' => $this->user()
        ], $props));
    }

    public function user()
    {
        return new User([
            'email' => 'mail@example.com'
        ]);
    }

    public function testAsset()
    {
        $avatar = $this->avatar();
        $this->assertEquals($avatar->url(), $avatar->asset()->url());
    }

    public function testClone()
    {
        $avatar = $this->avatar();
        $clone  = $avatar->clone();

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
            'user' => $user = new User(['email' => 'abc'])
        ]);

        $this->assertEquals($user, $avatar->user());
    }

}
