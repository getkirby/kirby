<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UsersViewController::class)]
class UsersViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.UsersViewController';

	public function setUp(): void
	{
		parent::setUp();
		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$this->app->impersonate('test');
	}

	public function testFactory(): void
	{
		$controller = UsersViewController::factory();
		$this->assertNull($controller->role);

		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'role' => 'admin'
				]
			]
		]);

		$controller = UsersViewController::factory();
		$this->assertSame('admin', $controller->role);
	}

	public function testLoad(): void
	{
		$controller = new UsersViewController();
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-users-view', $view->component);

		$props = $view->props();
		$this->assertNull($props['role']);
		$this->assertCount(1, $props['users']['data']);
	}

	public function testLoadWithRole(): void
	{
		$controller = new UsersViewController(role: 'admin');
		$props      = $controller->load()->props();
		$this->assertSame([
			'id'    => 'admin',
			'title' => 'Admin'
		], $props['role']);
	}

	public function testRole(): void
	{
		$controller = new UsersViewController();
		$this->assertNull($controller->role());

		$controller = new UsersViewController(role: 'admin');
		$this->assertSame([
			'id'    => 'admin',
			'title' => 'Admin'
		], $controller->role());
	}

	public function testRoles(): void
	{
		$controller = new UsersViewController();
		$this->assertSame([
			'admin' => [
				'id'    => 'admin',
				'title' => 'Admin'
			]
		], $controller->roles());
	}

	public function testUsers(): void
	{
		$controller = new UsersViewController();
		$users      = $controller->users();
		$this->assertCount(1, $users['data']);
		$this->assertSame('test@getkirby.com', $users['data'][0]['text']);
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
		], $users['pagination']);
	}

	public function testUsersWithRole(): void
	{
		$controller = new UsersViewController(role: 'foo');
		$users      = $controller->users();
		$this->assertCount(0, $users['data']);
	}
}
