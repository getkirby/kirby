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
					'methods' => ['password' => ['2fa' => true], 'code']
				]
			]
		]);

		$methods = $this->app->auth()->methods();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Auth method "code" is not enabled');
		$methods->authenticate('code', 'marge@simpsons.com', 'secret123');
	}

	public function testAuthenticateInvalid(): void
	{
		$methods = $this->app->auth()->methods();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Auth method "foo" is not enabled');
		$methods->authenticate('foo', 'marge@simpsons.com', 'secret123');
	}

	public function testConfigDefaults(): void
	{
		$methods = $this->app->auth()->methods();
		$this->assertSame(['password' => []], $methods->config());
	}

	public function testConfigNormalization(): void
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => [
						'password',
						'code'       => true,
						'basic-auth' => ['foo' => 'bar'],
					]
				]
			]
		]);

		$methods = $app->auth()->methods();

		$this->assertSame([
			'password'   => [],
			'code'       => [],
			'basic-auth' => ['foo' => 'bar'],
		], $methods->config());
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

	public function testEnabledWith2FA(): void
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

		$methods = $this->app->auth()->methods()->enabled();
		$this->assertSame(['password' => ['2fa' => true]], $methods);
	}

	public function testEnabledCodePasswordResetConflict(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['password', 'code', 'password-reset']
				]
			]
		]);

		$methods = $this->app->auth()->methods()->enabled();

		$this->assertSame([
			'password'       => [],
			'password-reset' => []
		], $methods);
	}

	public function testEnabledDebugRethrowsException(): void
	{
		$app = $this->app->clone([
			'options' => [
				'debug' => true,
				'auth' => [
					'methods' => [
						'password' => ['2fa' => true],
						'code'
					]
				]
			]
		]);

		$methods = $app->auth()->methods();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "code" login method cannot be enabled when 2FA is required');

		$methods->enabled();
	}

	public function testFirstEnabled(): void
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

		$methods = $app->auth()->methods();
		$this->assertInstanceOf(PasswordMethod::class, $methods->firstEnabled());
	}

	public function testFirstEnabledNone(): void
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
		$this->assertNull($methods->firstEnabled());
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
	}

	public function testHasEnabledMethods(): void
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

	public function testHasAnyUsingChallenges(): void
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
		$this->assertTrue($methods->hasAnyUsingChallenges());

		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['password', 'code']
				]
			]
		]);

		$methods = $app->auth()->methods();
		$this->assertTrue($methods->hasAnyUsingChallenges());

		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['password']
				]
			]
		]);

		$methods = $app->auth()->methods();
		$this->assertFalse($methods->hasAnyUsingChallenges());
	}

	public function testHasWithDisabled(): void
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
		$this->assertTrue($methods->has('password'));
		$this->assertFalse($methods->has('code'));
		$this->assertFalse($methods->has('password-reset'));
		$this->assertFalse($methods->has('unknown'));
	}
}
