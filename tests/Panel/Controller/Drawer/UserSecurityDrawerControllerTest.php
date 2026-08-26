<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Drawer;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserSecurityDrawerController::class)]
class UserSecurityDrawerControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Drawer.UserSecurityDrawerController';

	protected function setUp(): void
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

	public function testChallenges(): void
	{
		// no challenge is enabled on this site
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods'    => ['password'],
					'challenges' => []
				]
			]
		]);

		$user       = $this->app->user('test');
		$controller = new UserSecurityDrawerController($user);
		$this->assertSame([], $controller->challenges());

		// without enforced 2FA users can still opt into a second factor
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods'    => ['password'],
					'challenges' => ['totp']
				]
			]
		]);

		$this->app->impersonate('test');

		$user       = $this->app->user('test');
		$controller = new UserSecurityDrawerController($user);
		$challenges = $controller->challenges();

		$this->assertCount(1, $challenges);
		$this->assertSame('qr-code', $challenges[0]['icon']);


		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods'    => ['password' => ['2fa' => true]],
					'challenges' => ['email', 'totp'],
				]
			]
		]);

		// the account owner sees the TOTP setup entry for their own account
		$this->app->impersonate('test');

		$user       = $this->app->user('test');
		$controller = new UserSecurityDrawerController($user);
		$challenges = $controller->challenges();

		$this->assertCount(2, $challenges);
		$this->assertSame('email-unread', $challenges[0]['icon']);
		$this->assertSame($user->panel()->url(true) . '/security/challenge/email', $challenges[0]['drawer']);
		$this->assertSame('qr-code', $challenges[1]['icon']);
		$this->assertSame($user->panel()->url(true) . '/security/challenge/totp', $challenges[1]['drawer']);
	}

	public function testFactory(): void
	{
		$controller = UserSecurityDrawerController::factory('test');
		$this->assertInstanceOf(UserSecurityDrawerController::class, $controller);

		$drawer = $controller->load();
		$this->assertInstanceOf(Drawer::class, $drawer);

		$methods = $drawer->props()['methods'];
		$this->assertSame('Email', $methods[0]['text']);
	}

	public function testLoad(): void
	{
		$user       = $this->app->user('test');
		$controller = new UserSecurityDrawerController($user);
		$drawer     = $controller->load();

		$this->assertInstanceOf(Drawer::class, $drawer);
		$this->assertSame('k-user-security-drawer', $drawer->component);
		$this->assertSame(I18n::translate('security'), $drawer->title);

		$props = $drawer->props();
		$this->assertSame($controller->challenges(), $props['challenges']);
		$this->assertSame($controller->methods(), $props['methods']);
	}

	public function testMethods(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['password']
				]
			]
		]);

		$user       = $this->app->user('test');
		$controller = new UserSecurityDrawerController($user);
		$methods    = $controller->methods();

		$this->assertCount(2, $methods);
		$this->assertSame('email', $methods[0]['icon']);
		$this->assertSame($user->panel()->url(true) . '/changeEmail', $methods[0]['dialog']);
		$this->assertSame('key', $methods[1]['icon']);
		$this->assertSame($user->panel()->url(true) . '/changePassword', $methods[1]['dialog']);
	}
}
