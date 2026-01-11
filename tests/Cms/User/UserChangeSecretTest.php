<?php

namespace Kirby\Cms;

use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserChangeSecretTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.UserChangeSecret';

	protected User $admin;
	protected User $editor;

	public function setUp(): void
	{
		parent::setUp();

		$this->admin = new User([
			'id'    => 'admin',
			'email' => 'admin@domain.com',
			'role'  => 'admin'
		]);

		$this->app->users()->add($this->admin);


		$this->editor = new User([
			'id'    => 'editor',
			'email' => 'editor@domain.com',
			'role'  => 'editor'
		]);

		$this->app->users()->add($this->editor);
	}

	public function tearDown(): void
	{
		parent::tearDown();
		$this->app->session()->destroy();
		MockTime::reset();
	}

	public function testChangeSecret(): void
	{
		$file = static::TMP . '/site/accounts/admin/.htpasswd';
		F::write($file, 'a very secure hash');

		$user = $this->admin;
		$this->assertNull($user->secret('custom'));

		$user->changeSecret('custom', 'abc123');
		$this->assertSame(
			"a very secure hash\n" . '{"custom":"abc123"}',
			F::read($file)
		);
		$this->assertSame('abc123', $user->secret('custom'));

		$user->changeSecret('custom', null);
		$this->assertSame('a very secure hash', F::read($file));
		$this->assertNull($user->secret('custom'));
	}

	public function testChangeSecretKeepsUserLoggedIn(): void
	{
		$file = static::TMP . '/site/accounts/admin/.htpasswd';
		F::write($file, 'a very secure hash');

		$user = $this->admin;
		$user->loginPasswordless();

		$session   = $this->app->session();
		$token     = $session->token();
		$timestamp = $session->data()->get('kirby.loginTimestamp');
		$this->assertSame(MockTime::$time, $timestamp);

		MockTime::$time += 60;

		$user->changeSecret('custom', 'abc123');

		$session = $this->app->session();
		$this->assertSame($user, $this->app->user());
		$this->assertNotSame($token, $session->token());
		$this->assertSame(MockTime::$time, $session->get('kirby.loginTimestamp'));
	}

	public function testChangeSecretRuleNotPermitted(): void
	{
		$this->app->impersonate('editor@domain.com');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You cannot change user secrets for admin@domain.com');

		$this->admin->changeSecret('custom', 'abc123');
	}
}
