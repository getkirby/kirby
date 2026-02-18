<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Cms\User as ModelUser;
use Kirby\Content\Lock;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\Attributes\CoversClass;

class UserForceLocked extends ModelUser
{
	public function lock(): Lock
	{
		return new Lock(
			user: new ModelUser(['email' => 'test@getkirby.com']),
			modified: time()
		);
	}
}

#[CoversClass(\Kirby\Panel\User::class)]
#[CoversClass(Model::class)]
class UserTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP = KIRBY_TMP_DIR . '/Panel.User';

	public function setUp(): void
	{
		Blueprint::$loaded = [];

		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		$this->app->session()->destroy();
		Dir::remove(static::TMP);
	}

	protected function panel(array $props = [])
	{
		$user = new ModelUser(['id' => 'test', ...$props]);
		return new User($user);
	}

	public function testBreadcrumb(): void
	{
		$model = new ModelUser([
			'email' => 'test@getkirby.com',
		]);

		$breadcrumb = (new User($model))->breadcrumb();
		$this->assertSame('test@getkirby.com', $breadcrumb[0]['label']);
		$this->assertStringStartsWith('/users/', $breadcrumb[0]['link']);
	}

	public function testButtons(): void
	{
		$this->assertSame([
			'k-theme-view-button',
			'k-settings-view-button',
			'k-languages-view-button',
		], array_column($this->panel()->buttons(), 'component'));
	}

	public function testDropdownTotp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'options' => [
				'auth' => [
					'methods' => ['password' => ['2fa' => true]]
				]
			],
			'users' => [
				['email' => 'test@getkirby.com'],
				['email' => 'foo@getkirby.com']
			],
			'user' => 'test@getkirby.com'
		]);

		$user = $this->app->user();
		$dropdown = $user->panel()->dropdown();
		$this->assertSame('/account/totp/enable', $dropdown[7]['dialog']);
		$this->assertSame('qr-code', $dropdown[7]['icon']);

		$user->changeTotp('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
		$dropdown = $user->panel()->dropdown();
		$this->assertSame('/account/totp/disable', $dropdown[7]['dialog']);
	}

	public function testDropdownOption(): void
	{
		$model = new ModelUser([
			'id'    => 'test',
			'email' => 'test@getkirby.com',
		]);

		$option = (new User($model))->dropdownOption();
		$this->assertSame('user', $option['icon']);
		$this->assertSame('test@getkirby.com', $option['text']);
		$this->assertSame('/users/test', $option['link']);
	}

	public function testHome(): void
	{
		$user = new ModelUser([
			'email' => 'test@getkirby.com',
		]);

		$panel = new User($user);
		$this->assertSame('/panel/site', $panel->home());
	}

	public function testHomeWithCustomPath(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
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

	public function testHomeWithCustomPathQuery(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
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

	public function testImage(): void
	{
		$user = new ModelUser([
			'email' => 'test@getkirby.com',
		]);

		$image = (new User($user))->image();
		$this->assertSame('user', $image['icon']);
		$this->assertFalse(isset($image['url']));
	}

	public function testImageStringQuery(): void
	{
		$user = new ModelUser([
			'email' => 'test@getkirby.com',
		]);

		// fallback to model itself
		$image = (new User($user))->image('foo.bar');
		$this->assertNotEmpty($image);
	}

	public function testImageCover(): void
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

		$user = $app->user('test@getkirby.com');

		$testImage = static::FIXTURES . '/image/test.jpg';
		F::copy($testImage, $user->root() . '/test.jpg');

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

	public function testOptions(): void
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
			'changeRole'     => true,
			'delete'         => true,
			'unlock'         => true,
			'update'         => true,
		];

		$panel = new User($user);
		$this->assertSame($expected, $panel->options());
	}

	public function testOptionsWithLockedUser(): void
	{
		$user = new UserForceLocked([
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
			'unlock'         => false,
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
			'unlock'         => false,
			'update'         => false,
		];

		$this->assertSame($expected, $panel->options(['changeEmail']));
	}

	public function testPath(): void
	{
		$user = new ModelUser([
			'email' => 'test@getkirby.com',
		]);

		$panel = new User($user);
		$this->assertTrue(Str::startsWith($panel->path(), 'users/'));
	}

	public function testPickerDataDefault(): void
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

	public function testProps(): void
	{
		$user = new ModelUser([
			'email'    => 'test@getkirby.com',
			'language' => 'de'
		]);

		$panel = new User($user);
		$props = $panel->props();

		$this->assertArrayHasKey('model', $props);
		$this->assertArrayHasKey('avatar', $props['model']);
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
		$this->assertArrayHasKey('versions', $props);

		$this->assertNull($props['next']());
		$this->assertNull($props['prev']());
	}

	public function testPropsPrevNext(): void
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
		$this->assertSame('b@getkirby.com', $props['next']()['title']);

		$props = (new User($app->user('b@getkirby.com')))->props();
		$this->assertSame('a@getkirby.com', $props['prev']()['title']);
		$this->assertSame('c@getkirby.com', $props['next']()['title']);

		$props = (new User($app->user('c@getkirby.com')))->props();
		$this->assertSame('b@getkirby.com', $props['prev']()['title']);
		$this->assertNull($props['next']());
	}

	public function testTranslation(): void
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
		$this->assertNull($translations->get('translation.name'));
	}

	public function testPrevNext(): void
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
		$this->assertSame('b@getkirby.com', $prevNext['next']()['title']);

		$prevNext = (new User($app->user('b@getkirby.com')))->prevNext();
		$this->assertSame('a@getkirby.com', $prevNext['prev']()['title']);
		$this->assertSame('c@getkirby.com', $prevNext['next']()['title']);

		$prevNext = (new User($app->user('c@getkirby.com')))->prevNext();
		$this->assertSame('b@getkirby.com', $prevNext['prev']()['title']);
		$this->assertNull($prevNext['next']());
	}

	public function testPrevNextWithTab(): void
	{
		$app = $this->app->clone([
			'users' => [
				['id' => 'a', 'email' => 'a@getkirby.com'],
				['id' => 'b', 'email' => 'b@getkirby.com'],
				['id' => 'c', 'email' => 'c@getkirby.com']
			]
		]);

		$_GET['tab'] = 'test';

		$prevNext = (new User($app->user('b@getkirby.com')))->prevNext();
		$this->assertSame('/users/a?tab=test', $prevNext['prev']()['link']);
		$this->assertSame('/users/c?tab=test', $prevNext['next']()['link']);

		$_GET = [];
	}

	public function testView(): void
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
