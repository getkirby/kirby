<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Cms\Section;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Form\Field;
use Kirby\TestCase;

class UsersRoutesTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';
	public const string TMP      = KIRBY_TMP_DIR . '/Cms.UsersRoutes';

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
					'name'     => 'Bastian',
					'email'    => 'admin@getkirby.com',
					'role'     => 'admin',
					'password' => password_hash('12345678', PASSWORD_DEFAULT)
				],
				[
					'name'     => 'Sonja',
					'email'    => 'editor@getkirby.com',
					'role'     => 'admin',
					'password' => password_hash('87654321', PASSWORD_DEFAULT)
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function tearDown(): void
	{
		$this->app->session()->destroy();
		App::destroy();
		Field::$types = [];
		Section::$types = [];
		Dir::remove(static::TMP);
	}

	public function testAvatar(): void
	{
		// create an avatar for the user
		$this->app->user('admin@getkirby.com')->createFile([
			'filename' => 'profile.jpg',
			'source'   => static::FIXTURES . '/avatar.jpg',
			'template' => 'avatar',
		]);

		$response = $this->app->api()->call('users/admin@getkirby.com/avatar');

		$this->assertSame('profile.jpg', $response['data']['filename']);
	}

	public function testAvatarDelete(): void
	{
		// create an avatar for the user
		$this->app->user('admin@getkirby.com')->createFile([
			'filename' => 'profile.jpg',
			'source'   => static::FIXTURES . '/avatar.jpg',
			'template' => 'avatar',
		]);

		$response = $this->app->api()->call('users/admin@getkirby.com/avatar', 'DELETE');

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

		$app->impersonate('kirby');

		$response = $app->api()->call('users/admin@getkirby.com/blueprint');

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

		$app->impersonate('kirby');

		$response = $app->api()->call('users/admin@getkirby.com/blueprints');

		$this->assertCount(2, $response);
		$this->assertSame('Foo', $response[0]['title']);
		$this->assertSame('Bar', $response[1]['title']);
	}

	public function testCreate(): void
	{
		$app = $this->app;

		$response = $app->api()->call('users', 'POST', [
			'body' => [
				'email' => 'test@getkirby.com',
				'role'  => 'admin'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('model', $response['type']);
		$this->assertSame('admin', $response['data']['role']['name']);
		$this->assertSame('test@getkirby.com', $response['data']['username']);
	}

	public function testChangeEmail(): void
	{
		$response = $this->app->api()->call('users/admin@getkirby.com/email', 'PATCH', [
			'body' => [
				'email' => 'admin@getkirby.de'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('admin@getkirby.de', $response['data']['email']);
	}

	public function testChangeLanguage(): void
	{
		$response = $this->app->api()->call('users/admin@getkirby.com/language', 'PATCH', [
			'body' => [
				'language' => 'de'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('de', $response['data']['language']);
	}

	public function testChangeName(): void
	{
		$response = $this->app->api()->call('users/admin@getkirby.com/name', 'PATCH', [
			'body' => [
				'name' => 'Test user'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('Test user', $response['data']['name']);
	}

	public function testChangePassword(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$response = $this->app->api()->call('users/editor@getkirby.com/password', 'PATCH', [
			'body' => [
				'currentPassword' => '12345678',
				'password'        => 'super-secure-new-password'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertTrue($this->app->user('editor@getkirby.com')->validatePassword('super-secure-new-password'));
	}

	public function testChangePasswordMissingCurrentPassword(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid password. Passwords must be at least 8 characters long.');

		$this->app->impersonate('admin@getkirby.com');

		$this->app->api()->call('users/editor@getkirby.com/password', 'PATCH', [
			'body' => [
				'password' => 'super-secure-new-password'
			]
		]);
	}

	public function testChangePasswordWrongCurrentPassword(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Wrong password');

		$this->app->impersonate('admin@getkirby.com');

		$this->app->api()->call('users/editor@getkirby.com/password', 'PATCH', [
			'body' => [
				'currentPassword' => 'definitely-not-correct',
				'password'        => 'super-secure-new-password'
			]
		]);
	}

	public function testChangePasswordReset(): void
	{
		// the password reset mode of the acting user must not take effect when
		// changing the password of a different user

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid password. Passwords must be at least 8 characters long.');

		$this->app->impersonate('admin@getkirby.com');
		$this->app->session()->set('kirby.resetPassword', true);

		$this->app->api()->call('users/editor@getkirby.com/password', 'PATCH', [
			'body' => [
				'password' => 'super-secure-new-password'
			]
		]);
	}

	public function testChangeRole(): void
	{
		$response = $this->app->api()->call('users/editor@getkirby.com/role', 'PATCH', [
			'body' => [
				'role' => 'editor'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('editor', $response['data']['role']['name']);
	}

	public function testDelete(): void
	{
		$response = $this->app->api()->call('users/admin@getkirby.com', 'DELETE');
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

		$app->impersonate('kirby');

		$response = $app->api()->call('users/admin@getkirby.com/fields/test');

		$this->assertSame('Test home route', $response);

		$response = $app->api()->call('users/admin@getkirby.com/fields/test/nested');

		$this->assertSame('Test nested route', $response);
	}

	public function testFile(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'files' => [
						[
							'filename' => 'a.jpg',
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('users/test@getkirby.com/files/a.jpg');

		$this->assertSame('a.jpg', $response['data']['filename']);
	}

	public function testFiles(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
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

		$app->impersonate('kirby');

		$response = $app->api()->call('users/test@getkirby.com/files');

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

		$app->impersonate('kirby');

		$response = $app->api()->call('users/test@getkirby.com/files');

		$this->assertSame('b.jpg', $response['data'][0]['filename']);
		$this->assertSame('a.jpg', $response['data'][1]['filename']);
	}

	public function testGet(): void
	{
		$app = $this->app;

		$response = $app->api()->call('users/admin@getkirby.com');

		$this->assertSame('admin@getkirby.com', $response['data']['email']);
	}

	public function testRoles(): void
	{
		$this->app->impersonate('kirby');

		$response = $this->app->api()->call('users/admin@getkirby.com/roles');

		$this->assertSame(200, $response['code']);

		$this->assertCount(2, $response['data']);
		$this->assertSame('admin', $response['data'][0]['name']);
		$this->assertSame('editor', $response['data'][1]['name']);
	}

	public function testSearchName(): void
	{
		$app = $this->app;

		$response = $app->api()->call('users/search', 'GET', [
			'query' => [
				'q' => 'Bastian'
			]
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('admin@getkirby.com', $response['data'][0]['email']);
	}

	public function testSearchWithGetRequest(): void
	{
		$app = $this->app;

		$response = $app->api()->call('users/search', 'GET', [
			'query' => [
				'q' => 'editor'
			]
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('editor@getkirby.com', $response['data'][0]['email']);
	}

	public function testSearchWithPostRequest(): void
	{
		$app = $this->app;

		$response = $app->api()->call('users/search', 'POST', [
			'body' => [
				'search' => 'editor'
			]
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('editor@getkirby.com', $response['data'][0]['email']);
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

		$app->impersonate('kirby');

		$response = $app->api()->call('users/admin@getkirby.com/sections/test');
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
		$response = $this->app->api()->call('users/admin@getkirby.com', 'PATCH', [
			'body' => [
				'position' => 'Admin'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('Admin', $response['data']['content']['position']);
	}

	public function testUsers(): void
	{
		$response = $this->app->api()->call('users');

		$this->assertSame('admin@getkirby.com', $response['data'][0]['email']);
		$this->assertSame('editor@getkirby.com', $response['data'][1]['email']);
	}
}
