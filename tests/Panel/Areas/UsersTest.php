<?php

namespace Kirby\Panel\Areas;

class UsersTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
	}

	public function testUsersWithoutAuthentication(): void
	{
		$this->assertRedirect('users', 'login');
	}

	public function testUsers(): void
	{
		$this->login();

		$view  = $this->view('users');
		$props = $view['props'];

		$this->assertSame('users', $view['id']);
		$this->assertSame('k-users-view', $view['component']);
		$this->assertSame('Users', $view['title']);

		$this->assertNull($props['role']);
		$this->assertSame([
			[
				'id'    => 'admin',
				'title' => 'Admin'
			]
		], $props['roles']);

		$this->assertCount(1, $props['users']['data']);

		$this->assertSame([
			'page'      => 1,
			'firstPage' => 1,
			'lastPage'  => 1,
			'pages'     => 1,
			'offset'    => 0,
			'limit'     => 20,
			'total'     => 1,
			'start'     => 1,
			'end'       => 1
		], $props['users']['pagination']);
	}

	public function testUser(): void
	{
		$this->login();

		$view  = $this->view('users/test');
		$props = $view['props'];

		$this->assertSame('users', $view['id']);
		$this->assertSame('k-user-view', $view['component']);
		$this->assertSame('test@getkirby.com', $view['title']);
	}

	public function testUserWithMissingModel(): void
	{
		$this->login();
		$this->assertErrorView('users/does-not-exist', 'The user "does-not-exist" cannot be found');
	}

	public function testUserFiles(): void
	{
		$this->app([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'admin',
					'files' => [
						['filename' => 'test.jpg']
					]
				]
			]
		]);
		$this->login();

		$view  = $this->view('users/test/files/test.jpg');
		$props = $view['props'];

		$this->assertSame('users', $view['id']);
		$this->assertSame('k-file-view', $view['component']);
		$this->assertSame('test.jpg', $view['title']);

		// invalid request
		$view  = $this->view('users/test/files/no-exist.jpg');
		$props = $view['props'];

		$this->assertSame('users', $view['id']);
		$this->assertSame('k-error-view', $view['component']);
		$this->assertSame('Error', $view['title']);
		$this->assertSame('The file "no-exist.jpg" cannot be found', $props['error']);
	}

	public function testUsersWithInaccessibleRoles(): void
	{
		$this->app([
			'blueprints' => [
				'users/editor' => [
					'name'  => 'editor',
					'title' => 'Editor',
				],
				'users/secret' => [
					'name'    => 'secret',
					'title'   => 'Secret',
					'options' => [
						'access' => [
							'*' => false
						]
					]
				]
			],
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor',
				]
			]
		]);

		$this->login('editor@getkirby.com');

		$view  = $this->view('users');
		$props = $view['props'];

		$this->assertSame([
			['id' => 'admin',  'title' => 'Admin'],
			['id' => 'editor', 'title' => 'Editor'],
		], $props['roles']);
	}

	public function testUsersWithNonListableUsers(): void
	{
		// use uuid-based roles and user IDs to avoid static permission cache collisions
		$uuid = uuid();

		$this->app([
			'blueprints' => [
				'users/manager-' . $uuid => [
					'name' => 'manager-' . $uuid,
				],
				'users/restricted-' . $uuid => [
					'name'    => 'restricted-' . $uuid,
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'manager-' . $uuid],
				['name' => 'restricted-' . $uuid]
			],
			'users' => [
				[
					'id'    => 'manager-' . $uuid,
					'email' => 'manager@getkirby.com',
					'role'  => 'manager-' . $uuid
				],
				[
					'id'    => 'restricted-' . $uuid,
					'email' => 'restricted@getkirby.com',
					'role'  => 'restricted-' . $uuid
				]
			]
		]);
		$this->login('manager@getkirby.com');

		$view  = $this->view('users');
		$props = $view['props'];

		$this->assertCount(1, $props['users']['data']);
		$this->assertSame('manager@getkirby.com', $props['users']['data'][0]['text']);
	}

	public function testUsersWithRole(): void
	{
		$this->app([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'admin',
				],
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor',
				]
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'request' => [
				'query' => [
					'_json' => true,
					'role' => 'editor'
				]
			]
		]);
		$this->login();

		$view  = $this->view('users');
		$props = $view['props'];

		$this->assertSame([
			'id' => 'editor',
			'title' => 'Editor'
		], $props['role']);

		$this->assertSame([
			[
				'id' => 'admin',
				'title' => 'Admin'
			],
			[
				'id' => 'editor',
				'title' => 'Editor'
			]
		], $props['roles']);

		$users = $props['users'];
		$this->assertArrayHasKey('data', $users);
		$this->assertArrayHasKey('pagination', $users);
		$this->assertCount(1, $users['data']);
		$this->assertSame('editor', $users['data'][0]['id']);
	}
}
