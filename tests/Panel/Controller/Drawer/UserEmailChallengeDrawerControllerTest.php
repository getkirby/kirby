<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Cms\User;
use Kirby\Email\Email;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Drawer;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserEmailChallengeDrawerController::class)]
#[CoversClass(UserCredentialDrawerController::class)]
class UserEmailChallengeDrawerControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Drawer.UserEmailChallengeDrawerController';

	protected function setUp(): void
	{
		parent::setUp();

		Email::$debug  = true;
		Email::$emails = [];

		$this->app = $this->app->clone([
			// the challenge derives its sender address from the host
			'server' => [
				'SERVER_NAME' => 'getkirby.com'
			],
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

	protected function tearDown(): void
	{
		Email::$debug  = false;
		Email::$emails = [];

		parent::tearDown();
	}

	/**
	 * Opts the test user into the email challenge
	 */
	protected function enableEmailChallenge(): void
	{
		$this->app->user('test')->changeSecret('email', true);
	}

	/**
	 * Stores a pending challenge for the test user, just like
	 * the `code` action would have done before the user submits
	 */
	protected function storeCode(string $code = '123456'): void
	{
		$this->app->session()->set('kirby.security.authorize.test', [
			'public' => null,
			'secret' => User::hashPassword($code)
		]);
	}

	public function testFactory(): void
	{
		$controller = UserEmailChallengeDrawerController::factory('test');
		$this->assertInstanceOf(UserEmailChallengeDrawerController::class, $controller);
	}

	public function testLoad(): void
	{
		$this->app->impersonate('test');

		$user       = $this->app->user('test');
		$controller = new UserEmailChallengeDrawerController($user);
		$drawer     = $controller->load();

		$this->assertInstanceOf(Drawer::class, $drawer);
		$this->assertSame('k-user-email-challenge-drawer', $drawer->component);
		$this->assertSame('email-unread', $drawer->icon);
		$this->assertNotNull($drawer->title);

		$props = $drawer->props();
		$this->assertTrue($props['isAccount']);
		$this->assertFalse($props['isEnabled']);
		$this->assertSame(
			['avatar' => null, 'email' => 'test@getkirby.com', 'name' => 'Test User'],
			$props['user']
		);

		// opening the drawer must not send an email on its own
		$this->assertSame([], Email::$emails);
	}

	public function testLoadForOtherUser(): void
	{
		$this->enableEmailChallenge();
		$this->app->impersonate('admin');

		$controller = new UserEmailChallengeDrawerController($this->app->user('test'));
		$props      = $controller->load()->props();

		$this->assertFalse($props['isAccount']);
		$this->assertTrue($props['isEnabled']);
	}

	public function testSubmitCode(): void
	{
		$this->setRequest(['action' => 'code']);
		$this->app->impersonate('test');

		$result = (new UserEmailChallengeDrawerController($this->app->user('test')))->submit();

		$this->assertTrue($result);
		$this->assertCount(1, Email::$emails);
		$this->assertSame(
			['test@getkirby.com' => 'Test User'],
			Email::$emails[0]->to()
		);

		// the emailed code is kept for the following create/remove action
		$body = Email::$emails[0]->body()->text();
		preg_match('/[0-9]{3} [0-9]{3}/', $body, $code);
		$this->assertNotEmpty($code[0]);

		$pending = $this->app->session()->get('kirby.security.authorize.test');
		$this->assertTrue(
			password_verify(str_replace(' ', '', $code[0]), $pending['secret'])
		);
	}

	public function testSubmitCodeForOtherUser(): void
	{
		// an admin authorizes with their own password instead,
		// so no code must be sent to the other user
		$this->setRequest(['action' => 'code']);
		$this->app->impersonate('admin');

		$controller = new UserEmailChallengeDrawerController($this->app->user('test'));

		$this->assertTrue($controller->submit());
		$this->assertSame([], Email::$emails);
	}

	public function testSubmitCreate(): void
	{
		// the code is entered as it was formatted in the email
		$this->setRequest([
			'action'        => 'create',
			'authorization' => '123 456'
		]);
		$this->app->impersonate('test');
		$this->storeCode();

		$result = (new UserEmailChallengeDrawerController($this->app->user('test')))->submit();

		$this->assertTrue($result);
		$this->assertTrue($this->app->user('test')->secret('email'));
	}

	public function testSubmitCreateForOtherUser(): void
	{
		// an admin must not opt another user into the challenge:
		// only the account owner can prove their address is reachable
		$this->setRequest(['action' => 'create']);
		$this->app->impersonate('admin');

		$controller = new UserEmailChallengeDrawerController($this->app->user('test'));

		try {
			$controller->submit();
			$this->fail('Expected PermissionException was not thrown');
		} catch (PermissionException) {
			$this->assertNull($this->app->user('test')->secret('email'));
		}
	}

	public function testSubmitCreateWithWrongCode(): void
	{
		$this->setRequest([
			'action'        => 'create',
			'authorization' => '000 000'
		]);
		$this->app->impersonate('test');
		$this->storeCode();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.access.code');

		(new UserEmailChallengeDrawerController($this->app->user('test')))->submit();
	}

	public function testSubmitCreateWithoutCode(): void
	{
		// the code action must run first, otherwise there is
		// nothing to verify the user's input against
		$this->setRequest([
			'action'        => 'create',
			'authorization' => '000 000'
		]);
		$this->app->impersonate('test');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.access.code');

		(new UserEmailChallengeDrawerController($this->app->user('test')))->submit();
	}

	public function testSubmitRemoveAsAccount(): void
	{
		// the account owner opts out by entering a fresh code,
		// proving they still control the address
		$this->enableEmailChallenge();

		$this->setRequest([
			'action'        => 'remove',
			'authorization' => '123 456'
		]);
		$this->app->impersonate('test');
		$this->storeCode();

		$result = (new UserEmailChallengeDrawerController($this->app->user('test')))->submit();

		$this->assertTrue($result);
		$this->assertNull($this->app->user('test')->secret('email'));
	}

	public function testSubmitRemoveAsAccountWithWrongCode(): void
	{
		$this->enableEmailChallenge();

		$this->setRequest([
			'action'        => 'remove',
			'authorization' => '000 000'
		]);
		$this->app->impersonate('test');
		$this->storeCode();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.access.code');

		(new UserEmailChallengeDrawerController($this->app->user('test')))->submit();
	}

	public function testSubmitRemoveAsAdmin(): void
	{
		// an admin opts another user out with their own password,
		// e.g. when that user lost access to their mailbox
		$this->enableEmailChallenge();

		$this->setRequest([
			'action'   => 'remove',
			'password' => 'adminpass123'
		]);
		$this->app->impersonate('admin');

		$result = (new UserEmailChallengeDrawerController($this->app->user('test')))->submit();

		$this->assertTrue($result);
		$this->assertNull($this->app->user('test')->secret('email'));
	}

	public function testSubmitRemoveAsAdminWithWrongPassword(): void
	{
		$this->enableEmailChallenge();

		$this->setRequest([
			'action'   => 'remove',
			'password' => 'wrongpass'
		]);
		$this->app->impersonate('admin');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.password.wrong');

		(new UserEmailChallengeDrawerController($this->app->user('test')))->submit();
	}

	public function testSubmitWithInvalidAction(): void
	{
		$this->setRequest(['action' => 'nope']);
		$this->app->impersonate('kirby');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid action: nope');

		(new UserEmailChallengeDrawerController($this->app->user('test')))->submit();
	}
}
