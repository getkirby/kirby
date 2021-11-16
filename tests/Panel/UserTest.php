<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Cms\User as ModelUser;
use Kirby\Filesystem\Dir;
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
        Blueprint::$loaded = [];

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
     * @covers ::dropdownOption
     */
    public function testDropdownOption(): void
    {
        $model = new ModelUser([
            'id' => 'test',
            'email' => 'test@getkirby.com',
        ]);

        $option = (new User($model))->dropdownOption();
        $this->assertSame('user', $option['icon']);
        $this->assertSame('test@getkirby.com', $option['text']);
        $this->assertSame('/panel/users/test', $option['link']);
    }

    /**
     * @covers ::home
     */
    public function testHome()
    {
        $user = new ModelUser([
            'email' => 'test@getkirby.com',
        ]);

        $panel = new User($user);
        $this->assertSame('/panel/site', $panel->home());
    }

    /**
     * @covers ::home
     */
    public function testHomeWithCustomPath()
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->tmp,
            ],
            'blueprints' => [
                'users/editor' => [
                    'name' => 'editor',
                    'home' => '/blog'
                ]
            ]
        ]);

        $user = new ModelUser([
            'email' => 'test@getkirby.com',
            'role'  => 'editor'
        ]);

        $panel = new User($user);
        $this->assertSame('/blog', $panel->home());
    }

    /**
     * @covers ::home
     */
    public function testHomeWithCustomPathQuery()
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->tmp,
            ],
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ],
            'blueprints' => [
                'users/editor' => [
                    'name' => 'editor',
                    'home' => '{{ site.find("test").panel.url }}'
                ]
            ]
        ]);

        $user = new ModelUser([
            'email' => 'test@getkirby.com',
            'role'  => 'editor'
        ]);

        $panel = new User($user);
        $this->assertSame('/panel/pages/test', $panel->home());
    }

    /**
     * @covers ::imageDefaults
     * @covers ::imageSource
     */
    public function testImage()
    {
        $user = new ModelUser([
            'email' => 'test@getkirby.com',
        ]);

        $image = (new User($user))->image();
        $this->assertSame('user', $image['icon']);
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
            'back' => 'black',
            'color' => 'gray-500',
            'cover' => false,
            'icon' => 'user',
            'ratio' => '1/1',
            'url' => $mediaUrl . '/test.jpg',
            'src' => Model::imagePlaceholder(),
            'srcset' => $mediaUrl . '/test-38x.jpg 38w, ' . $mediaUrl . '/test-76x.jpg 76w'
        ], $panel->image());

        // cover enabled
        $this->assertSame([
            'back' => 'black',
            'color' => 'gray-500',
            'cover' => true,
            'icon' => 'user',
            'ratio' => '1/1',
            'url' => $mediaUrl . '/test.jpg',
            'src' => Model::imagePlaceholder(),
            'srcset' => $mediaUrl . '/test-38x38-crop.jpg 1x, ' . $mediaUrl . '/test-76x76-crop.jpg 2x'
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
        $this->assertArrayNotHasKey('tab', $props);
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

    /**
     * @covers ::prevNext
     */
    public function testPrevNext()
    {
        $app = $this->app->clone([
            'users' => [
                ['email' => 'a@getkirby.com'],
                ['email' => 'b@getkirby.com'],
                ['email' => 'c@getkirby.com']
            ]
        ]);

        $prevNext = (new User($app->user('a@getkirby.com')))->prevNext();
        $this->assertNull($prevNext['prev']());
        $this->assertSame('b@getkirby.com', $prevNext['next']()['tooltip']);

        $prevNext = (new User($app->user('b@getkirby.com')))->prevNext();
        $this->assertSame('a@getkirby.com', $prevNext['prev']()['tooltip']);
        $this->assertSame('c@getkirby.com', $prevNext['next']()['tooltip']);

        $prevNext = (new User($app->user('c@getkirby.com')))->prevNext();
        $this->assertSame('b@getkirby.com', $prevNext['prev']()['tooltip']);
        $this->assertNull($prevNext['next']());
    }

    /**
     * @covers ::view
     */
    public function testView()
    {
        $user = new ModelUser([
            'email' => 'test@getkirby.com',
        ]);

        $panel = new User($user);
        $view = $panel->view();

        $this->assertArrayHasKey('props', $view);
        $this->assertSame('k-user-view', $view['component']);
        $this->assertSame('test@getkirby.com', $view['title']);
        $this->assertSame('test@getkirby.com', $view['breadcrumb'][0]['label']);
    }
}
