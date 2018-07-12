<?php

namespace Kirby\Cms;

use Kirby\Image\Image;
use Kirby\Toolkit\Dir;

class AvatarTest extends TestCase
{

    const FIXTURES = __DIR__ . '/fixtures/avatars';

    public static $triggered = [];

    public function setUp()
    {
        static::$triggered = [];

        new App([
            'roots' => [
                'accounts' => static::FIXTURES . '/accounts',
                'media'    => static::FIXTURES . '/media',
            ],
            'hooks' => [
                'avatar.create:before' => function (Avatar $avatar, Upload $upload) {
                    AvatarTest::$triggered[] = 'avatar.create:before';
                },
                'avatar.create:after' => function (Avatar $avatar) {
                    AvatarTest::$triggered[] = 'avatar.create:after';
                },
                'avatar.delete:before' => function (Avatar $avatar) {
                    AvatarTest::$triggered[] = 'avatar.delete:before';
                },
                'avatar.delete:after' => function (bool $result) {
                    AvatarTest::$triggered[] = 'avatar.delete:after';
                },
                'avatar.replace:before' => function (Avatar $avatar, Upload $upload) {
                    AvatarTest::$triggered[] = 'avatar.replace:before';
                },
                'avatar.replace:after' => function (Avatar $avatar) {
                    AvatarTest::$triggered[] = 'avatar.replace:after';
                }
            ]
        ]);
    }

    public function tearDown()
    {
        Dir::remove(static::FIXTURES . '/accounts');
        Dir::remove(static::FIXTURES . '/media');
    }

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
        $this->assertInstanceOf(Image::class, $avatar->asset());
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
        $avatar = Avatar::create([
            'user'   => $this->user(),
            'source' => static::FIXTURES . '/test.jpg'
        ]);

        $this->assertFileExists($avatar->root());

        $this->assertTrue(in_array('avatar.create:before', static::$triggered));
        $this->assertTrue(in_array('avatar.create:after', static::$triggered));
    }

    public function testDelete()
    {
        Dir::make($this->user()->root());

        touch($this->avatar()->root());

        $this->assertFileExists($this->avatar()->root());

        $this->avatar()->delete();

        $this->assertFileNotExists($this->avatar()->root());

        $this->assertTrue(in_array('avatar.delete:before', static::$triggered));
        $this->assertTrue(in_array('avatar.delete:after', static::$triggered));
    }

    public function testFilename()
    {
        $avatar = $this->avatar();

        $this->assertEquals('profile.jpg', $avatar->filename());
    }

    public function testParent()
    {
        $this->assertInstanceOf(User::class, $this->avatar()->parent());
    }

    public function testReplace()
    {
        Dir::make($this->user()->root());

        touch($this->avatar()->root());

        $hashA = hash_file('md5', $this->avatar()->root());
        $hashB = hash_file('md5', static::FIXTURES . '/test.jpg');

        $this->assertNotEquals($hashA, $hashB);

        $this->avatar()->replace(static::FIXTURES . '/test.jpg');

        $hashC = hash_file('md5', $this->avatar()->root());

        $this->assertEquals($hashB, $hashC);

        $this->assertTrue(in_array('avatar.replace:before', static::$triggered));
        $this->assertTrue(in_array('avatar.replace:after', static::$triggered));
    }

    public function testRoot()
    {
        $avatar = $this->avatar();
        $this->assertEquals($this->user()->root() . '/profile.jpg', $avatar->root());
    }

    public function testUnpublish()
    {
        new App([
            'roots' => [
                'media' => $media = static::FIXTURES . '/media',
            ]
        ]);

        Dir::make($dir = $this->user()->mediaRoot());

        touch($image = $dir . '/profile.jpg');
        touch($thumb = $dir . '/profile-100x100.jpg');

        $this->assertFileExists($image);
        $this->assertFileExists($thumb);

        $this->avatar()->unpublish();

        $this->assertFileNotExists($image);
        $this->assertFileNotExists($thumb);

        Dir::remove($media);
    }

    public function testUrl()
    {
        $avatar = $this->avatar();
        $this->assertEquals('/media/users/f5c65c12abddabd8e5029f2189dc663884b332c0/profile.jpg', $avatar->url());

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
