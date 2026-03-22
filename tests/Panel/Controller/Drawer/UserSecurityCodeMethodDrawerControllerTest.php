<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Drawer\TextDrawer;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserSecurityCodeMethodDrawerController::class)]
class UserSecurityCodeMethodDrawerControllerTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'id'       => 'test',
					'email'    => 'test@getkirby.com',
					'role'     => 'admin',
					'password' => 'password123'
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		$controller = UserSecurityCodeMethodDrawerController::factory('test');
		$this->assertInstanceOf(UserSecurityCodeMethodDrawerController::class, $controller);
	}

	public function testLoad(): void
	{
		$user       = $this->app->user('test');
		$controller = new UserSecurityCodeMethodDrawerController($user);
		$drawer     = $controller->load();

		$this->assertInstanceOf(TextDrawer::class, $drawer);
		$this->assertSame('hashtag', $drawer->icon);
		$this->assertNotNull($drawer->text);
		$this->assertNotNull($drawer->title);
	}
}
