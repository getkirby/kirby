<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Auth\Challenge;
use Kirby\Auth\Challenge\TotpChallenge;
use Kirby\Auth\Pending;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\TestCase;
use Kirby\Toolkit\Totp;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * A challenge that returns real pending data, so the
 * authorization storage path can be asserted end to end
 */
class DummyCredentialChallenge extends Challenge
{
	public function create(): Pending|null
	{
		return new Pending(
			public: ['id' => 'pending-public'],
			secret: 'pending-secret'
		);
	}

	public function verify(mixed $input, Pending $data): bool
	{
		return $input === $data->secret();
	}
}

class DummyUserCredentialDrawerController extends UserCredentialDrawerController
{
	public function __construct(User $user, string $type = 'totp')
	{
		parent::__construct($user, $type);
	}

	public function authorization(): mixed
	{
		return parent::authorization();
	}

	public function authorize(): void
	{
		parent::authorize();
	}

	public function challenge(): Challenge
	{
		return parent::challenge();
	}

	public function isCurrentUser(): bool
	{
		return parent::isCurrentUser();
	}

	public function load(): mixed
	{
		return null;
	}
}

#[CoversClass(UserCredentialDrawerController::class)]
class UserCredentialDrawerControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Drawer.UserCredentialDrawerController';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'authChallenges' => [
				'dummy' => DummyCredentialChallenge::class
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

	protected function enableTotp(): string
	{
		$secret = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$this->app->user('test')->changeSecret('totp', $secret);
		return (new Totp($secret))->generate();
	}

	public function testAuthorization(): void
	{
		// the account owner gets a fresh challenge; the TOTP challenge
		// generates nothing itself, so an empty pending is stored
		$this->app->impersonate('test');

		$controller = new DummyUserCredentialDrawerController($this->app->user('test'), 'totp');
		$result     = $controller->authorization();

		$this->assertNull($result);
		$this->assertSame(
			['public' => null, 'secret' => null],
			$this->app->session()->get('kirby.security.authorize.test')
		);
	}

	public function testAuthorizationForOtherUser(): void
	{
		// an admin managing another user is not offered a challenge
		$this->app->impersonate('admin');

		$controller = new DummyUserCredentialDrawerController($this->app->user('test'));

		$this->assertNull($controller->authorization());
		$this->assertNull($this->app->session()->get('kirby.security.authorize.test'));
	}

	public function testAuthorizationWithChallengeData(): void
	{
		// a challenge that produces its own pending data has the public
		// part returned and both parts stored in the session
		$this->app->impersonate('test');

		$controller = new DummyUserCredentialDrawerController($this->app->user('test'), 'dummy');
		$result     = $controller->authorization();

		$this->assertSame(['id' => 'pending-public'], $result);
		$this->assertSame(
			[
				'public' => ['id' => 'pending-public'],
				'secret' => 'pending-secret'
			],
			$this->app->session()->get('kirby.security.authorize.test')
		);
	}

	public function testAuthorizeAsAdmin(): void
	{
		// an admin managing another user re-enters their own password
		$this->setRequest(['password' => 'adminpass123']);
		$this->app->impersonate('admin');

		$controller = new DummyUserCredentialDrawerController($this->app->user('test'));
		$controller->authorize();

		// no exception thrown and the admin stays authenticated
		$this->assertTrue($this->app->user()->is($this->app->user('admin')));
	}

	public function testAuthorizeAsAdminWithWrongPassword(): void
	{
		$this->setRequest(['password' => 'wrongpass']);
		$this->app->impersonate('admin');

		$controller = new DummyUserCredentialDrawerController($this->app->user('test'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.password.wrong');

		$controller->authorize();
	}

	public function testAuthorizeAsCurrentUser(): void
	{
		$code = $this->enableTotp();

		$this->setRequest(['authorization' => $code]);
		$this->app->impersonate('test');

		$controller = new DummyUserCredentialDrawerController($this->app->user('test'), 'totp');

		// seed the stored pending so we can prove it is pulled/consumed
		$this->app->session()->set(
			'kirby.security.authorize.test',
			['public' => null, 'secret' => null]
		);

		$controller->authorize();

		$this->assertNull($this->app->session()->get('kirby.security.authorize.test'));
	}

	public function testAuthorizeAsCurrentUserWithInvalidCode(): void
	{
		$this->enableTotp();

		$this->setRequest(['authorization' => '000000']);
		$this->app->impersonate('test');

		$controller = new DummyUserCredentialDrawerController($this->app->user('test'), 'totp');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.access.code');

		$controller->authorize();
	}

	public function testChallenge(): void
	{
		$controller = new DummyUserCredentialDrawerController($this->app->user('test'), 'totp');
		$challenge  = $controller->challenge();

		$this->assertInstanceOf(TotpChallenge::class, $challenge);
		$this->assertSame('2fa', $challenge->mode());
		$this->assertTrue($challenge->user()->is($this->app->user('test')));
	}

	public function testIsCurrentUser(): void
	{
		$this->app->impersonate('test');

		$controller = new DummyUserCredentialDrawerController($this->app->user('test'));
		$this->assertTrue($controller->isCurrentUser());
	}

	public function testIsCurrentUserForOtherUser(): void
	{
		$this->app->impersonate('admin');

		$controller = new DummyUserCredentialDrawerController($this->app->user('test'));
		$this->assertFalse($controller->isCurrentUser());
	}
}
