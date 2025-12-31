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

	public function testAuthenticateUnavailable(): void
	{
		$app = $this->app([
			'auth' => [
				'methods' => [
					'password' => ['2fa' => true],
					'code'     => []
				]
			]
		]);

		$methods = new Methods($app->auth(), $app);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Auth method "code" is not available');

		$methods->authenticate('code', 'mail@getkirby.com', 'secret', true);
	}

	public function testAvailableWith2FA(): void
	{
		$app = $this->app([
			'auth' => [
				'methods' => [
					'password' => ['2fa' => true],
					'code'     => []
				]
			]
		]);
		$methods = new Methods($app->auth(), $app);

		$this->assertSame([
			'password' => ['2fa' => true]
		], $methods->available());
	}

	public function testAvailableCodePasswordResetConflict(): void
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
		], $methods->available());
	}

	public function testHas(): void
	{
		$app = $this->app([
			'auth' => [
				'methods' => ['password', 'code']
			]
		]);

		$methods = new Methods($app->auth(), $app);

		$this->assertTrue($methods->has('password'));
		$this->assertTrue($methods->has('code'));
		$this->assertFalse($methods->has('password-reset'));
	}

	public function testHasAnyAvailableUsingChallenges(): void
	{
		$app = $this->app([
			'auth' => [
				'methods' => [
					'password' => ['2fa' => true],
				]
			]
		]);

		$methods = new Methods($app->auth(), $app);
		$this->assertTrue($methods->hasAnyAvailableUsingChallenges());

		$app = $this->app([
			'auth' => [
				'methods' => ['password', 'code']
			]
		]);

		$methods = new Methods($app->auth(), $app);
		$this->assertTrue($methods->hasAnyAvailableUsingChallenges());

		$app = $this->app([
			'auth' => [
				'methods' => ['password']
			]
		]);

		$methods = new Methods($app->auth(), $app);
		$this->assertFalse($methods->hasAnyAvailableUsingChallenges());
	}

	public function testHasAnyWith2FA(): void
	{
		$app = $this->app([
			'auth' => [
				'methods' => [
					'password' => ['2fa' => true],
					'code'     => []
				]
			]
		]);

		$methods = new Methods($app->auth(), $app);
		$this->assertTrue($methods->hasAnyWith2FA());

		$app = $this->app([
			'auth' => [
				'methods' => [
					'password' => [],
					'code'     => []
				]
			]
		]);

		$methods = new Methods($app->auth(), $app);
		$this->assertFalse($methods->hasAnyWith2FA());
	}

	public function testHasAvailable(): void
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

		$this->assertTrue($methods->hasAvailable('password'));
		$this->assertFalse($methods->hasAvailable('code'));
		$this->assertFalse($methods->hasAvailable('password-reset'));
		$this->assertFalse($methods->hasAvailable('unknown'));
	}

	public function testClass(): void
	{
		$methods = $this->methods();
		$this->assertSame(PasswordMethod::class, $methods->class('password'));
	}

	public function testClassInvalid(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('No auth method class for: unknown');

		$methods = $this->methods();
		$methods->class('unknown');
	}

	public function testEnabledDefaults(): void
	{
		$app     = $this->app();
		$methods = new Methods($app->auth(), $app);
		$this->assertSame(['password' => []], $methods->enabled());
	}

	public function testEnabledConfig(): void
	{
		$app = $this->app([
			'auth' => [
				'methods' => [
					'password'       => ['2fa' => true],
					'foo'            => [],
					'code'           => true,
					'password-reset'
				]
			]
		]);

		$methods = new Methods($app->auth(), $app);

		$this->assertSame([
			'password'       => ['2fa' => true],
			'foo'            => [],
			'code'           => [],
			'password-reset' => []
		], $methods->enabled());
	}
}
