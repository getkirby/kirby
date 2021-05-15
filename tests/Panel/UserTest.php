<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
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
     * @covers \Kirby\Panel\Model::icon
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
     * @covers \Kirby\Panel\Model::image
     * @covers \Kirby\Panel\Model::imageSource
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
     * @covers \Kirby\Panel\Model::image
     * @covers \Kirby\Panel\Model::imageSource
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
     * @covers ::imageSource
     * @covers \Kirby\Panel\Model::image
     * @covers \Kirby\Panel\Model::imageSource
     */
    public function testImageCover()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null',
                'media' => __DIR__ . '/tmp'
            ],
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'files' => [
                        [
                            'filename' => 'test.jpg',
                            'template' => 'avatar'
                        ]
                    ]
                ]
            ]
        ]);

        $user  = $app->user('test@getkirby.com');
        $panel = new User($user);

        $hash = $user->image()->mediaHash();
        $mediaUrl = $user->mediaUrl() . '/' . $hash;

        // cover disabled as default
        $this->assertSame([
            'ratio' => '3/2',
            'back' => 'pattern',
            'cover' => false,
            'url' => $mediaUrl . '/test.jpg',
            'cards' => [
                'url' => 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw',
                'srcset' => $mediaUrl . '/test-352x.jpg 352w, ' . $mediaUrl . '/test-864x.jpg 864w, ' . $mediaUrl . '/test-1408x.jpg 1408w'
            ],
            'list' => [
                'url' => 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw',
                'srcset' => $mediaUrl . '/test-38x.jpg 38w, ' . $mediaUrl . '/test-76x.jpg 76w'
            ]
        ], $panel->image());

        // cover enabled
        $this->assertSame([
            'ratio' => '3/2',
            'back' => 'pattern',
            'cover' => true,
            'url' => $mediaUrl . '/test.jpg',
            'cards' => [
                'url' => 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw',
                'srcset' => $mediaUrl . '/test-352x.jpg 352w, ' . $mediaUrl . '/test-864x.jpg 864w, ' . $mediaUrl . '/test-1408x.jpg 1408w'
            ],
            'list' => [
                'url' => 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw',
                'srcset' => $mediaUrl . '/test-38x38.jpg 1x, ' . $mediaUrl . '/test-76x76.jpg 2x'
            ]
        ], $panel->image(['cover' => true]));
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
     * @covers \Kirby\Panel\Model::__construct
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
