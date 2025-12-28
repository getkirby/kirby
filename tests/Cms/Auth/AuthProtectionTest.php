<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Auth::class)]
class AuthProtectionTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';
	public const string TMP      = KIRBY_TMP_DIR . '/Cms.AuthProtection';

	protected Auth $auth;
	public string|null $failedEmail = null;

	public function setUp(): void
	{
		$self = $this;

		$this->app = new App([
			'options' => [
				'auth' => [
					'debug' => false
				]
			],
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				[
					'email'    => 'marge@simpsons.com',
					'password' => password_hash('springfield123', PASSWORD_DEFAULT)
				],
				[
					'email'    => 'homer@simpsons.com',
					'password' => password_hash('springfield123', PASSWORD_DEFAULT)
				],
				[
					'email'    => 'test@exämple.com',
					'password' => password_hash('springfield123', PASSWORD_DEFAULT)
				]
			],
			'hooks' => [
				'user.login:failed' => function ($email) use ($self) {
					$self->failedEmail = $email;
				}
			]
		]);
		Dir::make(static::TMP . '/site/accounts');

		$this->auth = new Auth($this->app);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
		$this->failedEmail = null;
	}

	// rate-limit tests moved to kirby/tests/Auth/LimitsTest.php

	public function testValidatePasswordValid(): void
	{
		copy(static::FIXTURES . '/logins.json', static::TMP . '/site/accounts/.logins');

		$this->app->visitor()->ip('10.3.123.234');
		$user = $this->auth->validatePassword('marge@simpsons.com', 'springfield123');

		$this->assertIsUser($user);
		$this->assertSame('marge@simpsons.com', $user->email());
		$this->assertNull($this->failedEmail);
	}

	public function testValidatePasswordInvalid1(): void
	{
		copy(static::FIXTURES . '/logins.json', static::TMP . '/site/accounts/.logins');

		$this->app->visitor()->ip('10.3.123.234');

		$thrown = false;
		try {
			$this->auth->validatePassword('lisa@simpsons.com', 'springfield123');
		} catch (PermissionException $e) {
			$this->assertSame('Invalid login', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertSame(1, $this->auth->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
		$this->assertSame('lisa@simpsons.com', $this->failedEmail);
	}

	public function testValidatePasswordInvalid2(): void
	{
		copy(static::FIXTURES . '/logins.json', static::TMP . '/site/accounts/.logins');

		$this->app->visitor()->ip('10.3.123.234');

		$thrown = false;
		try {
			$this->auth->validatePassword('marge@simpsons.com', 'invalid-password');
		} catch (PermissionException $e) {
			$this->assertSame('Invalid login', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertSame(1, $this->auth->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
		$this->assertSame(1, $this->auth->log()['by-email']['marge@simpsons.com']['trials']);
		$this->assertSame('marge@simpsons.com', $this->failedEmail);
	}

	public function testValidatePasswordBlocked(): void
	{
		copy(static::FIXTURES . '/logins.json', static::TMP . '/site/accounts/.logins');

		$this->app->visitor()->ip('10.2.123.234');

		$thrown = false;
		try {
			$this->auth->validatePassword('homer@simpsons.com', 'springfield123');
		} catch (PermissionException $e) {
			$this->assertSame('Invalid login', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertSame('homer@simpsons.com', $this->failedEmail);
	}

	public function testValidatePasswordDebugInvalid1(): void
	{
		copy(static::FIXTURES . '/logins.json', static::TMP . '/site/accounts/.logins');
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'debug' => true
				]
			]
		]);
		$this->auth = new Auth($this->app);

		$this->app->visitor()->ip('10.3.123.234');

		$thrown = false;
		try {
			$this->auth->validatePassword('lisa@simpsons.com', 'springfield123');
		} catch (NotFoundException $e) {
			$this->assertSame('The user "lisa@simpsons.com" cannot be found', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertSame(1, $this->auth->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
		$this->assertSame('lisa@simpsons.com', $this->failedEmail);
	}

	public function testValidatePasswordDebugInvalid2(): void
	{
		copy(static::FIXTURES . '/logins.json', static::TMP . '/site/accounts/.logins');
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'debug' => true
				]
			]
		]);
		$this->auth = new Auth($this->app);

		$this->app->visitor()->ip('10.3.123.234');

		$thrown = false;
		try {
			$this->auth->validatePassword('marge@simpsons.com', 'invalid-password');
		} catch (InvalidArgumentException $e) {
			$this->assertSame('Wrong password', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertSame(1, $this->auth->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
		$this->assertSame(1, $this->auth->log()['by-email']['marge@simpsons.com']['trials']);
		$this->assertSame('marge@simpsons.com', $this->failedEmail);
	}

	public function testValidatePasswordDebugBlocked(): void
	{
		copy(static::FIXTURES . '/logins.json', static::TMP . '/site/accounts/.logins');
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'debug' => true
				]
			]
		]);
		$this->auth = new Auth($this->app);

		$this->app->visitor()->ip('10.2.123.234');

		$thrown = false;
		try {
			$this->auth->validatePassword('homer@simpsons.com', 'springfield123');
		} catch (PermissionException $e) {
			$this->assertSame('Rate limit exceeded', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertSame('homer@simpsons.com', $this->failedEmail);
	}

	public function testValidatePasswordWithUnicodeEmail(): void
	{
		copy(static::FIXTURES . '/logins.json', static::TMP . '/site/accounts/.logins');

		$this->app->visitor()->ip('10.3.123.234');
		$user = $this->auth->validatePassword('test@exämple.com', 'springfield123');

		$this->assertIsUser($user);
		$this->assertSame('test@exämple.com', $user->email());
	}

	public function testValidatePasswordWithPunycodeEmail(): void
	{
		copy(static::FIXTURES . '/logins.json', static::TMP . '/site/accounts/.logins');

		$this->app->visitor()->ip('10.3.123.234');
		$user = $this->auth->validatePassword('test@xn--exmple-cua.com', 'springfield123');

		$this->assertIsUser($user);
		$this->assertSame('test@exämple.com', $user->email());
	}
}
