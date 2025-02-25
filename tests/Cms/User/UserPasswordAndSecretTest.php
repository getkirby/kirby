<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(User::class)]
class UserPasswordAndSecretTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserPasswordAndSecret';

	public function testPasswordTimestamp(): void
	{
		// create a user file
		F::write(static::TMP . '/site/accounts/test/index.php', '<?php return [];');

		$user = $this->app->user('test');
		$this->assertNull($user->passwordTimestamp());

		// create a password file
		$file = static::TMP . '/site/accounts/test/.htpasswd';
		F::write($file, 'a very secure hash');
		touch($file, 1337000000);

		$this->assertSame(1337000000, $user->passwordTimestamp());

		// timestamp is not cached
		touch($file, 1338000000);
		$this->assertSame(1338000000, $user->passwordTimestamp());
	}

	public function testSecret(): void
	{
		F::write(static::TMP . '/site/accounts/test/index.php', '<?php return [];');
		$user = $this->app->user('test');

		// no secrets file
		$this->assertNull($user->secret('password'));
		$this->assertNull($user->secret('totp'));
		$this->assertNull($user->secret('invalid'));

		// just a password hash
		$file = static::TMP . '/site/accounts/test/.htpasswd';
		F::write($file, 'a very secure hash');
		$this->assertSame('a very secure hash', $user->secret('password'));
		$this->assertNull($user->secret('totp'));
		$this->assertNull($user->secret('invalid'));

		// extra secrets
		F::write($file, 'a very secure hash' . "\n" . '{"totp":"foo"}');
		$this->assertSame('a very secure hash', $user->secret('password'));
		$this->assertSame('foo', $user->secret('totp'));
		$this->assertNull($user->secret('invalid'));

		// just extra secrets
		F::write($file, "\n" . '{"totp":"foo"}');
		$this->assertNull($user->secret('password'));
		$this->assertSame('foo', $user->secret('totp'));
		$this->assertNull($user->secret('invalid'));

		// invalid JSON
		F::write($file, "\n" . 'this is not JSON');
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('JSON string is invalid');
		$user->secret('totp');
	}

	public static function passwordProvider(): array
	{
		return [
			[null, false],
			['', false],
			['short', false],
			[str_repeat('long', 300), false],
			['invalid-password', false],
			['correct-horse-battery-staple', true],
		];
	}

	#[DataProvider('passwordProvider')]
	public function testValidatePassword($input, $valid): void
	{
		$user = new User([
			'email'    => 'test@getkirby.com',
			'password' => User::hashPassword('correct-horse-battery-staple')
		]);

		if ($valid === false) {
			$this->expectException(InvalidArgumentException::class);
			$user->validatePassword($input);
		} else {
			$this->assertTrue($user->validatePassword($input));
		}
	}

	public function testValidatePasswordHttpCode(): void
	{
		$user = new User([
			'email'    => 'test@getkirby.com',
			'password' => User::hashPassword('correct-horse-battery-staple')
		]);

		$caught = 0;

		try {
			$user->validatePassword('short');
		} catch (InvalidArgumentException $e) {
			$this->assertSame(
				'Please enter a valid password. Passwords must be at least 8 characters long.',
				$e->getMessage()
			);
			$this->assertSame(400, $e->getHttpCode());
			$caught++;
		}

		try {
			$user->validatePassword(str_repeat('long', 300));
		} catch (InvalidArgumentException $e) {
			$this->assertSame(
				'Please enter a valid password. Passwords must not be longer than 1000 characters.',
				$e->getMessage()
			);
			$this->assertSame(400, $e->getHttpCode());
			$caught++;
		}

		try {
			$user->validatePassword('longbutinvalid');
		} catch (InvalidArgumentException $e) {
			$this->assertSame('Wrong password', $e->getMessage());
			$this->assertSame(401, $e->getHttpCode());
			$caught++;
		}

		$this->assertSame(3, $caught);
	}

	public function testValidateUndefinedPassword(): void
	{
		$user = new User([
			'email' => 'test@getkirby.com',
		]);

		$this->expectException(NotFoundException::class);
		$user->validatePassword('test');
	}
}
