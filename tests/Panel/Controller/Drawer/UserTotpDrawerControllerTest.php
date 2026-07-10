<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Drawer;
use Kirby\Toolkit\Totp;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserTotpDrawerController::class)]
#[CoversClass(UserCredentialDrawerController::class)]
class UserTotpDrawerControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Drawer.UserTotpDrawerController';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'id'       => 'test',
					'name'     => 'Test User',
					'email'    => 'test@getkirby.com',
					'role'     => 'admin',
					'password' => User::hashPassword('password123')
				],
				[
					'id'       => 'admin',
					'email'    => 'admin@getkirby.com',
					'role'     => 'admin',
					'password' => User::hashPassword('adminpass123')
				]
			],
			'site' => [
				'title' => 'Test Site'
			]
		]);

		$this->app->impersonate('kirby');
	}

	/**
	 * Enables TOTP for the test user and returns a currently valid code
	 */
	protected function enableTotp(): string
	{
		$secret = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$this->app->user('test')->changeSecret('totp', $secret);
		return (new Totp($secret))->generate();
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
		$this->assertFalse($props['isEnabled']);
		$this->assertStringContainsString('data:image/png;base64,', $props['qr']);
		$this->assertSame('Test User', $props['user']);
		$this->assertSame(32, strlen($props['value']['secret']));

		// the account owner confirms removal by re-entering a code
		$this->assertTrue($props['isAccount']);
	}

	public function testLoadForOtherUser(): void
	{
		// an admin managing another user only confirms the action
		$this->app->impersonate('admin');

		$props = (new UserTotpDrawerController($this->app->user('test')))->load()->props();
		$this->assertFalse($props['isAccount']);
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
		$this->app->impersonate('test');

		$controller = new UserTotpDrawerController($this->app->user('test'));
		$result     = $controller->submit();

		$this->assertTrue($result);
		$this->assertSame($secret, $this->app->user('test')->secret('totp'));
	}

	public function testSubmitCreateForOtherUser(): void
	{
		// an admin must not enable TOTP for another user: the factor would
		// live on the admin's device and lock the user out at next login
		$secret  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$confirm = (new Totp($secret))->generate();

		$this->setRequest([
			'action'  => 'create',
			'secret'  => $secret,
			'confirm' => $confirm,
		]);
		$this->app->impersonate('admin');

		$controller = new UserTotpDrawerController($this->app->user('test'));

		try {
			$controller->submit();
			$this->fail('Expected PermissionException was not thrown');
		} catch (PermissionException) {
			// the target user's account must be left untouched
			$this->assertNull($this->app->user('test')->secret('totp'));
		}
	}

	public function testSubmitCreateWithMissingConfirm(): void
	{
		$this->setRequest([
			'action' => 'create',
			'secret' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'
		]);
		$this->app->impersonate('test');

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
		$this->app->impersonate('test');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.login.totp.confirm.invalid');

		$controller = new UserTotpDrawerController($this->app->user('test'));
		$controller->submit();
	}

	public function testSubmitRemoveAsAccount(): void
	{
		// the account owner disables TOTP by entering a current code,
		// proving they still control the factor
		$code = $this->enableTotp();

		$this->setRequest([
			'action'        => 'remove',
			'authorization' => $code
		]);
		$this->app->impersonate('test');

		$result = (new UserTotpDrawerController($this->app->user('test')))->submit();

		$this->assertTrue($result);
		$this->assertNull($this->app->user('test')->secret('totp'));
	}

	public function testSubmitRemoveAsAccountWithWrongCode(): void
	{
		$this->enableTotp();

		$this->setRequest([
			'action'        => 'remove',
			'authorization' => '000000'
		]);
		$this->app->impersonate('test');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.access.code');

		(new UserTotpDrawerController($this->app->user('test')))->submit();
	}

	public function testSubmitRemoveAsAdmin(): void
	{
		// an admin disables TOTP for another user with their own password
		$this->enableTotp();

		$this->setRequest([
			'action'   => 'remove',
			'password' => 'adminpass123'
		]);
		$this->app->impersonate('admin');

		$result = (new UserTotpDrawerController($this->app->user('test')))->submit();

		$this->assertTrue($result);
		$this->assertNull($this->app->user('test')->secret('totp'));
	}

	public function testSubmitRemoveAsAdminWithWrongPassword(): void
	{
		$this->enableTotp();

		$this->setRequest([
			'action'   => 'remove',
			'password' => 'wrongpass'
		]);
		$this->app->impersonate('admin');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.password.wrong');

		(new UserTotpDrawerController($this->app->user('test')))->submit();
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
