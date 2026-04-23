<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;

class UserApiModelTest extends ApiModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserApiModel';

	protected User $user;

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = new User(['email' => 'test@getkirby.com']);
	}

	public function testFiles(): void
	{
		$this->app->impersonate('kirby');

		$user = new User([
			'email' => 'test@getkirby.com',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
			]
		]);

		$this->app->impersonate('kirby');

		$model = $this->api->resolve($user)->select('files')->toArray();

		$this->assertSame('a.jpg', $model['files'][0]['filename']);
		$this->assertSame('b.jpg', $model['files'][1]['filename']);
	}

	public function testImage(): void
	{
		$image = $this->attr($this->user, 'panelImage');
		$expected = [
			'back' => 'black',
			'color' => 'gray-500',
			'cover' => false,
			'icon'  => 'user',
			'ratio' => '1/1'
		];

		$this->assertSame($expected, $image);
	}

	public function testInaccessibleRolePermissions(): void
	{
		$uuid = uuid();

		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'users/restricted-' . $uuid => [
					'options' => [
						'access' => [
							'editor-' . $uuid => true,
							'*'      => false
						]
					]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid],
				['name' => 'restricted-' . $uuid],
			],
			'users' => [
				['email' => 'editor@test.com', 'role' => 'editor-' . $uuid],
				['email' => 'restricted@test.com', 'role' => 'restricted-' . $uuid],
			]
		]);

		$this->api = $this->app->api();

		$restrictedUser = $this->app->user('restricted@test.com');

		// editor can see permissions of the restricted role
		$this->app->impersonate('editor@test.com');
		$model = $this->api->resolve($restrictedUser)->select('permissions')->toArray();
		$this->assertNotNull($model['permissions']);

		// restricted user cannot see permissions of their own inaccessible role
		$this->app->impersonate('restricted@test.com');
		$model = $this->api->resolve($restrictedUser)->select('permissions')->toArray();
		$this->assertNull($model['permissions']);
	}

	public function testNextSkipsInaccessibleUser(): void
	{
		$uuid = uuid();

		$app = new App([
			'blueprints' => [
				'users/restricted-' . $uuid => [
					'options' => ['access' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid],
				['name' => 'restricted-' . $uuid],
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				['email' => 'a@test.com', 'name' => 'A User', 'role' => 'editor-' . $uuid],
				['email' => 'b@test.com', 'name' => 'B User', 'role' => 'editor-' . $uuid],
				['email' => 'c@test.com', 'name' => 'C User', 'role' => 'restricted-' . $uuid],
				['email' => 'd@test.com', 'name' => 'D User', 'role' => 'editor-' . $uuid],
			]
		]);

		$app->impersonate('b@test.com');
		$user   = $app->user('b@test.com');
		$result = $app->api()->resolve($user)->select('next')->toArray();

		$this->assertSame('D User', $result['next']['name']);
	}

	public function testInaccessibleRole(): void
	{
		$uuid = uuid();

		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'users/restricted-' . $uuid => [
					'options' => [
						'access' => [
							'editor-' . $uuid => true,
							'*'      => false
						]
					]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid],
				['name' => 'restricted-' . $uuid],
			],
			'users' => [
				['email' => 'editor@test.com', 'role' => 'editor-' . $uuid],
				['email' => 'restricted@test.com', 'role' => 'restricted-' . $uuid],
			]
		]);

		$this->api = $this->app->api();

		$restrictedUser = $this->app->user('restricted@test.com');

		// editor can see the restricted role
		$this->app->impersonate('editor@test.com');
		$model = $this->api->resolve($restrictedUser)->select('role')->toArray();
		$this->assertNotNull($model['role']);

		// restricted user cannot see their own role
		$this->app->impersonate('restricted@test.com');
		$model = $this->api->resolve($restrictedUser)->select('role')->toArray();
		$this->assertNull($model['role']);
	}

	public function testPrevSkipsInaccessibleUser(): void
	{
		$uuid = uuid();

		$app = new App([
			'blueprints' => [
				'users/restricted-' . $uuid => [
					'options' => ['access' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid],
				['name' => 'restricted-' . $uuid],
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				['email' => 'a@test.com', 'name' => 'A User', 'role' => 'editor-' . $uuid],
				['email' => 'b@test.com', 'name' => 'B User', 'role' => 'restricted-' . $uuid],
				['email' => 'c@test.com', 'name' => 'C User', 'role' => 'editor-' . $uuid],
				['email' => 'd@test.com', 'name' => 'D User', 'role' => 'editor-' . $uuid],
			]
		]);

		$app->impersonate('c@test.com');
		$user   = $app->user('c@test.com');
		$result = $app->api()->resolve($user)->select('prev')->toArray();

		$this->assertSame('A User', $result['prev']['name']);
	}
}
