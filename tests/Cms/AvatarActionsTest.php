<?php

namespace Kirby\Cms;

class ReplaceableTestAvatar extends Avatar
{
    public function mime()
    {
        return 'image/jpeg';
    }
}

class AvatarActionsTestStore extends AvatarStoreDefault
{

    public static $exists = true;

    public function create(Upload $upload)
    {
        return $this->avatar();
    }

    public function delete(): bool
    {
        static::$exists = false;
        return true;
    }

    public function exists(): bool
    {
        return static::$exists;
    }

}

class AvatarActionsTest extends TestCase
{

    public function user()
    {
        return new User([
            'email' => 'test@test.com',
        ]);
    }

    public function avatar()
    {
        return new Avatar([
            'user' => $this->user(),
            'store' => AvatarActionsTestStore::class
        ]);
    }

    public function testCreate()
    {
        AvatarActionsTestStore::$exists = false;

        $user = $this->user();

        $this->assertHooks([
            'avatar.create:before' => function (Avatar $avatar, Upload $upload) use ($user) {
                $this->assertEquals('profile.jpg', $avatar->filename());
                $this->assertEquals($user, $avatar->user());
            },
            'avatar.create:after' => function (Avatar $avatar) use ($user) {
                $this->assertEquals('profile.jpg', $avatar->filename());
                $this->assertEquals($user, $avatar->user());
            }
        ], function () use ($user) {
            $result = Avatar::create([
                'source' => __DIR__ . '/fixtures/files/test.jpg',
                'user'   => $user,
                'store'  => AvatarActionsTestStore::class
            ]);

            $this->assertEquals('profile.jpg', $result->filename());
            $this->assertEquals($user, $result->user());
        });

    }

    public function testDelete()
    {
        AvatarActionsTestStore::$exists = true;

        $this->assertHooks([
            'avatar.delete:before' => function (Avatar $avatar) {
                $this->assertTrue($avatar->exists());
            },
            'avatar.delete:after' => function (bool $result, Avatar $avatar) {
                $this->assertFalse($avatar->exists());
                $this->assertTrue($result);
            }
        ], function () {
            $this->avatar()->delete();
        });
    }

    public function testReplace()
    {
        $avatar = new ReplaceableTestAvatar([
            'user'  => $this->user(),
            'store' => AvatarActionsTestStore::class
        ]);

        $this->assertHooks([
            'avatar.replace:before' => function (Avatar $avatar, Upload $upload) {
                $this->assertEquals('test.jpg', $upload->filename());
                $this->assertEquals('profile.jpg', $avatar->filename());
            },
            'avatar.replace:after' => function (Avatar $newAvatar, Avatar $oldAvatar) {
            }
        ], function () use ($avatar) {
            $avatar->replace(__DIR__ . '/fixtures/files/test.jpg');
        });
    }

}
