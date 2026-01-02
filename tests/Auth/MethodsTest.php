<?php

namespace Kirby\Auth;

use Kirby\Api\Api;
use Kirby\Auth\Method\CodeMethod;
use Kirby\Auth\Method\PasswordMethod;
use Kirby\Cms\Auth\Status;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Methods::class)]
class MethodsTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Methods';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'email'    => 'marge@simpsons.com',
					'id'       => 'marge',
					'password' => User::hashPassword('secret123')
				],
			]
		]);
	}

	public function testAuthenticate(): void
	{
		$methods = $this->app->auth()->methods();
		$result  = $methods->authenticate('password', 'marge@simpsons.com', 'secret123');

		$this->assertInstanceOf(User::class, $result);
		$this->assertSame('marge', $result->id());
	}

	public function testAuthenticateUnavailable(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['code']
				]
			]
		]);

		$methods = $this->app->auth()->methods();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Auth method "password" is not available');
		$methods->authenticate('password', 'marge@simpsons.com', 'secret123');
	}

	public function testAuthenticateInvalid(): void
	{
		$methods = $this->app->auth()->methods();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Auth method "foo" is not available');
		$methods->authenticate('foo', 'marge@simpsons.com', 'secret123');
	}

	public function testAvailableWith2FA(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => [
						'password' => ['2fa' => true],
						'code'     => []
					]
				]
			]
		]);

		$methods = $this->app->auth()->methods()->available();
		$this->assertSame(['password' => ['2fa' => true]], $methods);
	}

	public function testAvailableCodePasswordResetConflict(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['password', 'code', 'password-reset']
				]
			]
		]);

		$methods = $this->app->auth()->methods()->available();

		$this->assertSame([
			'password'       => [],
			'password-reset' => []
		], $methods);
	}

	public function testAuthenticateApiRequest(): void
	{
		$api = $this->createStub(Api::class);
		$api->method('requestBody')->willReturnCallback(
			fn ($key) => match ($key) {
				'email'    => 'marge@simpsons.com',
				'password' => 'secret123',
				'long'     => false
			}
		);

		$methods = $this->app->auth()->methods();
		$result  = $methods->authenticateApiRequest($api);
		$this->assertSame($this->app->user('marge'), $result);
	}

	public function testAuthenticateApiRequestMissingPassword(): void
	{
		$api = $this->createStub(Api::class);
		$api->method('requestBody')->willReturnCallback(
			fn ($key) => match ($key) {
				'email'    => 'marge@simpsons.com',
				'password' => '',
				'long'     => false
			}
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Login without password is not enabled');

		$methods = $this->app->auth()->methods();
		$methods->authenticateApiRequest($api);
	}

	public function testAuthenticateApiRequestCode(): void
	{

		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['code']
				]
			]
		]);

		$api = $this->createStub(Api::class);
		$api->method('requestBody')->willReturnCallback(
			fn ($key) => match ($key) {
				'email'    => 'marge@simpsons.com',
				'password' => '',
				'long'     => false
			}
		);

		$methods = $this->app->auth()->methods();
		$result  = $methods->authenticateApiRequest($api);

		$this->assertInstanceOf(Status::class, $result);
		$this->assertSame('marge@simpsons.com', $result->email());
		$this->assertSame('login', $result->mode());
	}

	public function testClass(): void
	{
		$methods = $this->app->auth()->methods();
		$this->assertSame(PasswordMethod::class, $methods->class('password'));
	}

	public function testClassInvalid(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('No auth method class for: unknown');

		$methods = $this->app->auth()->methods();
		$methods->class('unknown');
	}

	public function testEnabledDefaults(): void
	{
		$methods = $this->app->auth()->methods();
		$this->assertSame(['password' => []], $methods->enabled());
	}

	public function testEnabledConfig(): void
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => [
						'password'       => ['2fa' => true],
						'foo'            => [],
						'code'           => true,
						'password-reset'
					]
				]
			]
		]);

		$methods = $app->auth()->methods();

		$this->assertSame([
			'password'       => ['2fa' => true],
			'foo'            => [],
			'code'           => [],
			'password-reset' => []
		], $methods->enabled());
	}

	public function testFirstAvailable(): void
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => [
						'code',
						'password' => ['2fa' => true],
					]
				]
			]
		]);

		$methods = $this->app->auth()->methods();
		$this->assertInstanceOf(PasswordMethod::class, $methods->firstAvailable());
	}

	public function testFirstAvailableNone(): void
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => [
						'code' => ['2fa' => true]
					]
				]
			]
		]);

		$methods = $app->auth()->methods();
		$this->assertNull($methods->firstAvailable());
	}

	public function testGet(): void
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['password', 'code']
				]
			]
		]);

		$methods = $app->auth()->methods();
		$this->assertInstanceOf(PasswordMethod::class, $methods->get('password'));
		$this->assertInstanceOf(CodeMethod::class, $methods->get('code'));
		$this->assertNull($methods->get('foo'));
	}

	public function testHas(): void
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['password', 'code']
				]
			]
		]);

		$methods = $app->auth()->methods();
		$this->assertTrue($methods->has('password'));
		$this->assertTrue($methods->has('code'));
		$this->assertFalse($methods->has('password-reset'));
	}

	public function testHasAnyAvailableUsingChallenges(): void
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => [
						'password' => ['2fa' => true],
					]
				]
			]
		]);

		$methods = $app->auth()->methods();
		$this->assertTrue($methods->hasAnyAvailableUsingChallenges());

		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['password', 'code']
				]
			]
		]);

		$methods = $app->auth()->methods();
		$this->assertTrue($methods->hasAnyAvailableUsingChallenges());

		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['password']
				]
			]
		]);

		$methods = $app->auth()->methods();
		$this->assertFalse($methods->hasAnyAvailableUsingChallenges());
	}

	public function testHasAnyWith2FA(): void
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => [
						'password' => ['2fa' => true],
						'code'     => []
					]
				]
			]
		]);

		$methods = $app->auth()->methods();
		$this->assertTrue($methods->hasAnyWith2FA());

		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => [
						'password' => [],
						'code'     => []
					]
				]
			]
		]);

		$methods = $app->auth()->methods();
		$this->assertFalse($methods->hasAnyWith2FA());
	}

	public function testHasAvailable(): void
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => [
						'password'       => ['2fa' => true],
						'code'           => true,
						'password-reset' => []
					]
				]
			]
		]);

		$methods = $app->auth()->methods();
		$this->assertTrue($methods->hasAvailable('password'));
		$this->assertFalse($methods->hasAvailable('code'));
		$this->assertFalse($methods->hasAvailable('password-reset'));
		$this->assertFalse($methods->hasAvailable('unknown'));
	}
}
