<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\User as ModelUser;
use Kirby\Content\Lock;
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

#[CoversClass(User::class)]
#[CoversClass(Model::class)]
class UserTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.User';

	protected function panel(array $props = [])
	{
		$user = new ModelUser(['id' => 'test', ...$props]);
		return new User($user);
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
}
