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
    protected $app;
    protected $tmp = __DIR__ . '/tmp';

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->tmp,
            ]
        ]);

        Dir::make($this->tmp);
    }

    public function tearDown(): void
    {
        Dir::remove($this->tmp);
    }

    /**
     * @covers ::breadcrumb
     */
    public function testBreadcrumb(): void
    {
        $model = new ModelUser([
            'email' => 'test@getkirby.com',
        ]);

        $breadcrumb = (new User($model))->breadcrumb();
        $this->assertSame('test@getkirby.com', $breadcrumb[0]['label']);
        $this->assertStringStartsWith('/users/', $breadcrumb[0]['link']);
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
     * @covers ::imageSource
     * @covers \Kirby\Panel\Model::image
     * @covers \Kirby\Panel\Model::imageSource
     */
    public function testImageCover()
    {
        $app = $this->app->clone([
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
                'url' => Model::imagePlaceholder(),
                'srcset' => $mediaUrl . '/test-352x.jpg 352w, ' . $mediaUrl . '/test-864x.jpg 864w, ' . $mediaUrl . '/test-1408x.jpg 1408w'
            ],
            'list' => [
                'url' => Model::imagePlaceholder(),
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
                'url' => Model::imagePlaceholder(),
                'srcset' => $mediaUrl . '/test-352x.jpg 352w, ' . $mediaUrl . '/test-864x.jpg 864w, ' . $mediaUrl . '/test-1408x.jpg 1408w'
            ],
            'list' => [
                'url' => Model::imagePlaceholder(),
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

    /**
     * @covers ::route
     */
    public function testRoute()
    {
        $user = new ModelUser([
            'email' => 'test@getkirby.com',
        ]);

        $panel = new User($user);
        $route = $panel->route();

        $this->assertArrayHasKey('props', $route);
        $this->assertSame('k-user-view', $route['component']);
        $this->assertSame('test@getkirby.com', $route['title']);
        $this->assertSame('test@getkirby.com', $route['breadcrumb'][0]['label']);
    }

    /**
     * @covers ::props
     */
    public function testProps()
    {
        $user = new ModelUser([
            'email'    => 'test@getkirby.com',
            'language' => 'de'
        ]);

        $panel = new User($user);
        $props = $panel->props();

        $this->assertArrayHasKey('model', $props);
        $this->assertArrayHasKey('avatar', $props['model']);
        $this->assertArrayHasKey('content', $props['model']);
        $this->assertArrayHasKey('email', $props['model']);
        $this->assertArrayHasKey('id', $props['model']);
        $this->assertArrayHasKey('language', $props['model']);
        $this->assertArrayHasKey('name', $props['model']);
        $this->assertArrayHasKey('role', $props['model']);
        $this->assertArrayHasKey('username', $props['model']);

        // inherited props
        $this->assertArrayHasKey('blueprint', $props);
        $this->assertArrayHasKey('lock', $props);
        $this->assertArrayHasKey('permissions', $props);
        $this->assertArrayHasKey('tab', $props);
        $this->assertArrayHasKey('tabs', $props);

        $this->assertNull($props['next']());
        $this->assertNull($props['prev']());
    }

    /**
     * @covers ::props
     */
    public function testPropsPrevNext()
    {
        $app = $this->app->clone([
            'users' => [
                ['email' => 'a@getkirby.com'],
                ['email' => 'b@getkirby.com'],
                ['email' => 'c@getkirby.com']
            ]
        ]);

        $props = (new User($app->user('a@getkirby.com')))->props();
        $this->assertNull($props['prev']());
        $this->assertSame('b@getkirby.com', $props['next']()['tooltip']);

        $props = (new User($app->user('b@getkirby.com')))->props();
        $this->assertSame('a@getkirby.com', $props['prev']()['tooltip']);
        $this->assertSame('c@getkirby.com', $props['next']()['tooltip']);

        $props = (new User($app->user('c@getkirby.com')))->props();
        $this->assertSame('b@getkirby.com', $props['prev']()['tooltip']);
        $this->assertNull($props['next']());
    }

    /**
     * @covers ::translation
     */
    public function testTranslation()
    {
        // existing
        $user = new ModelUser([
            'email'    => 'test@getkirby.com',
            'language' => 'de'
        ]);

        $panel = new User($user);
        $translations = $panel->translation();
        $this->assertSame('de', $translations->code());
        $this->assertSame('Deutsch', $translations->get('translation.name'));

        // non-existing
        $user = new ModelUser([
            'email'    => 'test@getkirby.com',
            'language' => 'foo'
        ]);

        $panel = new User($user);
        $translations = $panel->translation();
        $this->assertSame('foo', $translations->code());
        $this->assertSame(null, $translations->get('translation.name'));
    }
}
