<?php

namespace Kirby\Auth;

use Kirby\Auth\Method\PasswordMethod;
use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\Auth\Status;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

class DummyStatus extends Status
{
	public function __construct()
	{
	}
}

class DummyMethod extends Method
{
	public static array $calls = [];
	protected Status $status;

	public function __construct(Auth $auth, array $options = [])
	{
		parent::__construct($auth, $options);
		$this->status = $options['status'] ?? new DummyStatus();
	}

	public function authenticate(string $email, string|null $password = null, bool $long = false): Status
	{
		self::$calls[] = func_get_args();
		return $this->status;
	}
}

#[CoversClass(Methods::class)]
class MethodsTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Methods';

	protected function app(array $options = []): App
	{
		return new App([
			'roots' => [
				'index' => static::TMP
			],
			'options' => $options
		]);
	}

	protected function methods(): Methods
	{
		$app = $this->app();
		return new Methods($app->auth(), $app);
	}

	public function testAuthenticate(): void
	{
		$status = new DummyStatus();

		DummyMethod::$calls = [];
		Methods::$methods   = ['dummy' => DummyMethod::class];

		$app = $this->app([
			'auth' => [
				'methods' => [
					'dummy' => ['status' => $status]
				]
			]
		]);
		$methods = new Methods($app->auth(), $app);
		$result  = $methods->authenticate('dummy', 'mail@getkirby.com', 'secret', true);

		$this->assertSame($status, $result);
		$this->assertSame([['mail@getkirby.com', 'secret', true]], DummyMethod::$calls);
	}

	public function testClass(): void
	{
		$methods = $this->methods();
		$this->assertSame(PasswordMethod::class, $methods->class('password'));
	}

	public function testClassInvalid(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Unsupported auth method: unknown');

		$methods = $this->methods();
		$methods->class('unknown');
	}

	public function testEnabledDefaults(): void
	{
		$app     = $this->app();
		$methods = new Methods($app->auth(), $app);
		$this->assertSame(['password' => []], $methods->enabled());
	}

	public function testEnabledWith2FA(): void
	{
		$app = $this->app([
			'auth' => [
				'methods' => [
					'password'       => ['2fa' => true],
					'code'           => true,
					'password-reset' => []
				]
			]
		]);

		$methods = new Methods($app->auth(), $app);

		$this->assertSame([
			'password' => ['2fa' => true]
		], $methods->enabled());
	}

	public function testEnabledWith2FADebug(): void
	{
		$app = $this->app([
			'debug' => true,
			'auth'  => [
				'methods' => [
					'password' => ['2fa' => true],
					'code'     => []
				]
			]
		]);
		$methods = new Methods($app->auth(), $app);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "code" login method cannot be enabled when 2FA is required');

		$methods->enabled();
	}

	public function testEnabledCodePasswordResetConflict(): void
	{
		$app = $this->app([
			'auth' => [
				'methods' => ['password', 'code', 'password-reset']
			]
		]);
		$methods = new Methods($app->auth(), $app);

		$this->assertSame([
			'password'       => [],
			'password-reset' => []
		], $methods->enabled());
	}

	public function testEnabledCodePasswordResetConflictDebug(): void
	{
		$app = $this->app([
			'debug' => true,
			'auth' => [
				'methods' => ['password', 'code', 'password-reset']
			]
		]);
		$methods = new Methods($app->auth(), $app);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "code" and "password-reset" login methods cannot be enabled together');

		$methods->enabled();
	}
}
