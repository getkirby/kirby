<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Drawer;
use Kirby\Toolkit\Totp;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserTotpDrawerController::class)]
class UserTotpDrawerControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Drawer.UserTotpDrawerController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'id'       => 'test',
					'name'     => 'Test User',
					'email'    => 'test@getkirby.com',
					'role'     => 'admin',
					'password' => 'password123'
				],
				[
					'id'       => 'other',
					'email'    => 'other@getkirby.com',
					'role'     => 'admin',
					'password' => 'password123'
				]
			],
			'site' => [
				'title' => 'Test Site'
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		$controller = UserTotpDrawerController::factory('test');
		$this->assertInstanceOf(UserTotpDrawerController::class, $controller);
	}

	public function testLoad(): void
	{
		$this->app->impersonate('test');

		$user       = $this->app->user('test');
		$controller = new UserTotpDrawerController($user);
		$drawer     = $controller->load();

		$this->assertInstanceOf(Drawer::class, $drawer);
		$this->assertSame('k-user-totp-drawer', $drawer->component);
		$this->assertSame('qr-code', $drawer->icon);
		$this->assertNotNull($drawer->title);

		$props = $drawer->props();
		$this->assertTrue($props['isAccount']);
		$this->assertFalse($props['isEnabled']);
		$this->assertStringContainsString('<svg', $props['qr']);
		$this->assertSame('Test User', $props['user']);
		$this->assertSame(32, strlen($props['value']['secret']));
	}

	public function testSubmitCreate(): void
	{
		$secret  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$confirm = (new Totp($secret))->generate();

		$this->setRequest([
			'action'  => 'create',
			'secret'  => $secret,
			'confirm' => $confirm,
		]);
		$this->app->impersonate('kirby');

		$controller = new UserTotpDrawerController($this->app->user('test'));
		$result     = $controller->submit();

		$this->assertTrue($result);
		$this->assertSame($secret, $this->app->user('test')->secret('totp'));
	}

	public function testSubmitCreateWithMissingConfirm(): void
	{
		$this->setRequest([
			'action' => 'create',
			'secret' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'
		]);
		$this->app->impersonate('kirby');

		$this->expectException(InvalidArgumentException::class);

		$controller = new UserTotpDrawerController($this->app->user('test'));
		$controller->submit();
	}

	public function testSubmitCreateWithInvalidConfirm(): void
	{
		$this->setRequest([
			'action'  => 'create',
			'secret'  => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567',
			'confirm' => (new Totp('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'))->generate()
		]);
		$this->app->impersonate('kirby');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.login.totp.confirm.invalid');

		$controller = new UserTotpDrawerController($this->app->user('test'));
		$controller->submit();
	}

	public function testSubmitRemove(): void
	{
		$secret = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$this->app->user('test')->changeSecret('totp', $secret);

		$this->setRequest([
			'action' => 'remove'
		]);
		$this->app->impersonate('kirby');

		$user       = $this->app->user('test');
		$controller = new UserTotpDrawerController($user);
		$result     = $controller->submit();

		$this->assertTrue($result);
		$this->assertNull($this->app->user('test')->secret('totp'));
	}

	public function testSubmitRemoveWithWrongPassword(): void
	{
		$secret = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$this->app->user('test')->changeSecret('totp', $secret);

		$this->setRequest([
			'action'   => 'remove',
			'password' => 'wrongpass'
		]);
		$this->app->impersonate('test');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.password.wrong');

		$controller = new UserTotpDrawerController($this->app->user('test'));
		$controller->submit();
	}

	public function testSubmitWithInvalidAction(): void
	{
		$this->setRequest([
			'action' => 'nope'
		]);
		$this->app->impersonate('kirby');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid action: nope');

		$controller = new UserTotpDrawerController($this->app->user('test'));
		$controller->submit();
	}
}
