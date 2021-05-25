<?php

namespace Kirby\Panel;

use Kirby\Cms\User as ModelUser;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\TestCase;

class ModelUserTestForceLocked extends ModelUser
{
    public function isLocked(): bool
    {
        return true;
    }
}

/**
 * @coversDefaultClass \Kirby\Panel\User
 */
class UserTest extends TestCase
{
    public function tearDown(): void
    {
        Dir::remove(__DIR__ . '/tmp');
    }

    /**
     * @covers ::icon
     */
    public function testIconDefault()
    {
        $user = new ModelUser([
            'email' => 'test@getkirby.com',
        ]);

        $icon = (new User($user))->icon();

        $this->assertSame([
            'type'  => 'user',
            'ratio' => null,
            'back'  => 'pattern',
            'color' => '#c5c9c6'
        ], $icon);
    }

    /**
     * @covers ::imageSource
     */
    public function testImage()
    {
        $user = new ModelUser([
            'email' => 'test@getkirby.com',
        ]);

        $image = (new User($user))->image();
        $this->assertFalse(isset($image['url']));
    }

    /**
     * @covers ::imageSource
     */
    public function testImageStringQuery()
    {
        $user = new ModelUser([
            'email' => 'test@getkirby.com',
        ]);

        // fallback to model itself
        $image = (new User($user))->image('foo.bar');
        $this->assertFalse(empty($image));
    }

    /**
     * @covers \Kirby\Panel\Model::options
     */
    public function testOptions()
    {
        $user = new ModelUser([
            'email' => 'test@getkirby.com',
        ]);

        $user->kirby()->impersonate('kirby');

        $expected = [
            'create'         => true,
            'changeEmail'    => true,
            'changeLanguage' => true,
            'changeName'     => true,
            'changePassword' => true,
            'changeRole'     => false, // just one role
            'delete'         => true,
            'update'         => true,
        ];

        $panel = new User($user);
        $this->assertSame($expected, $panel->options());
    }

    /**
     * @covers \Kirby\Panel\Model::options
     */
    public function testOptionsWithLockedUser()
    {
        $user = new ModelUserTestForceLocked([
            'email' => 'test@getkirby.com',
        ]);

        $user->kirby()->impersonate('kirby');

        // without override
        $expected = [
            'create'         => false,
            'changeEmail'    => false,
            'changeLanguage' => false,
            'changeName'     => false,
            'changePassword' => false,
            'changeRole'     => false,
            'delete'         => false,
            'update'         => false,
        ];

        $panel = new User($user);
        $this->assertSame($expected, $panel->options());

        // with override
        $expected = [
            'create'         => false,
            'changeEmail'    => true,
            'changeLanguage' => false,
            'changeName'     => false,
            'changePassword' => false,
            'changeRole'     => false,
            'delete'         => false,
            'update'         => false,
        ];

        $this->assertSame($expected, $panel->options(['changeEmail']));
    }

    /**
     * @covers ::path
     */
    public function testPath()
    {
        $user = new ModelUser([
            'email' => 'test@getkirby.com',
        ]);

        $panel = new User($user);
        $this->assertTrue(Str::startsWith($panel->path(), 'users/'));
    }

    /**
     * @covers ::pickerData
     * @covers \Kirby\Panel\Model::pickerData
     */
    public function testPickerDataDefault()
    {
        $user = new ModelUser([
            'email' => 'test@getkirby.com',
        ]);

        $panel = new User($user);
        $data  = $panel->pickerData();

        $this->assertSame('test@getkirby.com', $data['email']);
        $this->assertTrue(Str::startsWith($data['link'], '/users/'));
        $this->assertSame('test@getkirby.com', $data['text']);
    }
}
