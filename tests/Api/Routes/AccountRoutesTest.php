<?php

namespace Kirby\Api;

use Kirby\Blueprint\Blueprint;
use Kirby\Blueprint\Section;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Form\Field;
use Kirby\TestCase;

class AccountRoutesTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';
	public const string TMP      = KIRBY_TMP_DIR . '/Cms.AccountRoutes';

	public function setUp(): void
	{
		Blueprint::$loaded = [];

		$this->app = new App([
			'options' => [
				'api.allowImpersonation' => true
			],
			'roles' => [
				[
					'name' => 'admin'
				],
				[
					'name' => 'editor'
				]
			],
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				[
					'email'    => 'test@getkirby.com',
					'role'     => 'admin',
					'password' => password_hash('12345678', PASSWORD_DEFAULT)
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		$this->app->session()->destroy();
		Field::$types = [];
		Section::$types = [];
		Dir::remove(static::TMP);
		App::destroy();
	}

	public function testAvatar(): void
	{
		// create an avatar for the user
		$this->app->user()->createFile([
			'filename' => 'profile.jpg',
			'source'   => static::FIXTURES . '/avatar.jpg',
			'template' => 'avatar',
		]);

		$response = $this->app->api()->call('account/avatar');

		$this->assertSame('profile.jpg', $response['data']['filename']);
	}

	public function testAvatarDelete(): void
	{
		// create an avatar for the user
		$this->app->user()->createFile([
			'filename' => 'profile.jpg',
			'source'   => static::FIXTURES . '/avatar.jpg',
			'template' => 'avatar',
		]);

		$response = $this->app->api()->call('account/avatar', 'DELETE');

		$this->assertTrue($response);
	}

	public function testBlueprint(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/admin' => [
					'name'  => 'admin',
					'title' => 'Test'
				]
			]
		]);

		$app->impersonate('test@getkirby.com');

		$response = $app->api()->call('account/blueprint');

		$this->assertSame(200, $response['code']);
		$this->assertSame('Test', $response['data']['title']);
		$this->assertSame('admin', $response['data']['name']);
	}

	public function testBlueprints(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/admin' => [
					'name'     => 'admin',
					'title'    => 'Admin',
					'sections' => [
						'test' => [
							'type'   => 'pages',
							'parent' => 'site',
							'templates' => [
								'foo',
								'bar'
							]
						]
					]
				],
				'users/editor' => [
					'name'  => 'editor',
					'title' => 'Editor'
				]
			]
		]);

		$app->impersonate('test@getkirby.com');

		$response = $app->api()->call('account/blueprints');

		$this->assertCount(2, $response);
		$this->assertSame('Foo', $response[0]['title']);
		$this->assertSame('Bar', $response[1]['title']);
	}

	public function testChangeEmail(): void
	{
		$response = $this->app->api()->call('account/email', 'PATCH', [
			'body' => [
				'email' => 'admin@getkirby.de'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('admin@getkirby.de', $response['data']['email']);
	}

	public function testChangeLanguage(): void
	{
		$response = $this->app->api()->call('account/language', 'PATCH', [
			'body' => [
				'language' => 'de'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('de', $response['data']['language']);
	}

	public function testChangeName(): void
	{
		$response = $this->app->api()->call('account/name', 'PATCH', [
			'body' => [
				'name' => 'Test user'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('Test user', $response['data']['name']);
	}

	public function testChangePassword(): void
	{
		$response = $this->app->api()->call('account/password', 'PATCH', [
			'body' => [
				'currentPassword' => '12345678',
				'password'        => 'super-secure-new-password'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertTrue($this->app->user()->validatePassword('super-secure-new-password'));
	}

	public function testChangePasswordMissingCurrentPassword(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid password. Passwords must be at least 8 characters long.');

		$this->app->api()->call('account/password', 'PATCH', [
			'body' => [
				'password' => 'super-secure-new-password'
			]
		]);
	}

	public function testChangePasswordWrongCurrentPassword(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Wrong password');

		$this->app->api()->call('account/password', 'PATCH', [
			'body' => [
				'currentPassword' => 'definitely-not-correct',
				'password'        => 'super-secure-new-password'
			]
		]);
	}

	public function testChangePasswordReset(): void
	{
		$this->app->session()->set('kirby.resetPassword', true);

		$response = $this->app->api()->call('account/password', 'PATCH', [
			'body' => [
				'password' => 'super-secure-new-password'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertTrue($this->app->user()->validatePassword('super-secure-new-password'));
		$this->assertNull($this->app->session()->get('kirby.resetPassword'));
	}

	public function testChangeRole(): void
	{
		$response = $this->app->api()->call('account/role', 'PATCH', [
			'body' => [
				'role' => 'editor'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('editor', $response['data']['role']['name']);
	}

	public function testDelete(): void
	{
		$response = $this->app->api()->call('account', 'DELETE');
		$this->assertTrue($response);
	}

	public function testFields(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/admin' => [
					'fields' => [
						'test' => [
							'type' => 'test'
						]
					]
				]
			],
			'fields' => [
				'test' => [
					'api' => fn () => [
						[
							'pattern' => '/',
							'action'  => fn () => 'Test home route'
						],
						[
							'pattern' => 'nested',
							'action'  => fn () => 'Test nested route'
						],
					]
				]
			]
		]);

		$app->impersonate('test@getkirby.com');

		$response = $app->api()->call('account/fields/test');

		$this->assertSame('Test home route', $response);

		$response = $app->api()->call('account/fields/test/nested');

		$this->assertSame('Test nested route', $response);
	}

	public function testFile(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin',
					'files' => [
						[
							'filename' => 'a.jpg',
						]
					]
				]
			]
		]);

		$app->impersonate('test@getkirby.com');

		$response = $app->api()->call('account/files/a.jpg');

		$this->assertSame('a.jpg', $response['data']['filename']);
	}

	public function testFiles(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin',
					'files' => [
						[
							'filename' => 'c.jpg',
						],
						[
							'filename' => 'a.jpg',
						],
						[
							'filename' => 'b.jpg',
						]
					]
				]
			]
		]);

		$app->impersonate('test@getkirby.com');

		$response = $app->api()->call('account/files');

		$this->assertCount(3, $response['data']);
		$this->assertSame('a.jpg', $response['data'][0]['filename']);
		$this->assertSame('b.jpg', $response['data'][1]['filename']);
		$this->assertSame('c.jpg', $response['data'][2]['filename']);
	}

	public function testFilesSorted(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin',
					'files' => [
						[
							'filename' => 'a.jpg',
							'content'  => [
								'sort' => 2
							]
						],
						[
							'filename' => 'b.jpg',
							'content'  => [
								'sort' => 1
							]
						]
					]
				]
			]
		]);

		$app->impersonate('test@getkirby.com');

		$response = $app->api()->call('account/files');

		$this->assertSame('b.jpg', $response['data'][0]['filename']);
		$this->assertSame('a.jpg', $response['data'][1]['filename']);
	}

	public function testGet(): void
	{
		$app = $this->app;

		$response = $app->api()->call('account');

		$this->assertSame('test@getkirby.com', $response['data']['email']);
	}

	public function testRoles(): void
	{
		$response = $this->app->api()->call('account/roles');

		$this->assertSame(200, $response['code']);

		$this->assertCount(2, $response['data']);
		$this->assertSame('admin', $response['data'][0]['name']);
		$this->assertSame('editor', $response['data'][1]['name']);
	}

	public function testSections(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/admin' => [
					'sections' => [
						'test' => [
							'type' => 'test'
						]
					]
				]
			],
			'sections' => [
				'test' => [
					'toArray' => fn () => [
						'foo' => 'bar'
					]
				]
			]
		]);

		$app->impersonate('test@getkirby.com');

		$response = $app->api()->call('account/sections/test');
		$expected = [
			'status' => 'ok',
			'code'   => 200,
			'name'   => 'test',
			'type'   => 'test',
			'foo'    => 'bar'
		];

		$this->assertSame($expected, $response);
	}

	public function testUpdate(): void
	{
		$response = $this->app->api()->call('account', 'PATCH', [
			'body' => [
				'position' => 'Admin'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('Admin', $response['data']['content']['position']);
	}
}
