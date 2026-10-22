<?php

namespace Kirby\Auth;

use Kirby\Auth\Exception\RateLimitException;
use Kirby\Auth\Method\CodeMethod;
use Kirby\Auth\Method\PasswordMethod;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;

class BrokenMethod extends Method
{
	public function __construct(
		Auth $auth,
		string $type,
		array $options = []
	) {
		throw new InvalidArgumentException(message: 'Missing client');
	}

	public function authenticate(
		string|null $email,
		string|null $password = null,
		bool $long = false
	): User|Status {
		return new User(['email' => $email]);
	}
}

/**
 * One class registered under a type per provider
 */
class ProviderMethod extends Method
{
	public function authenticate(
		string|null $email,
		string|null $password = null,
		bool $long = false
	): User|Status {
		return new User(['email' => $email]);
	}

	public function isEnabled(): bool
	{
		return $this->type() !== 'oauth-gitlab';
	}
}

#[CoversClass(Methods::class)]
class MethodsTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Methods';

	protected function setUp(): void
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

		Methods::$methods['broken']       = BrokenMethod::class;
		Methods::$methods['oauth-github'] = ProviderMethod::class;
		Methods::$methods['oauth-gitlab'] = ProviderMethod::class;
	}

	protected function tearDown(): void
	{
		unset(
			Methods::$methods['broken'],
			Methods::$methods['oauth-github'],
			Methods::$methods['oauth-gitlab']
		);

		parent::tearDown();
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

	public function testAuthenticateBlockedIp(): void
	{
		$accounts = static::TMP . '/site/accounts';
		Dir::make($accounts);
		copy(static::FIXTURES . '/logins.json', $accounts . '/.logins');

		// IP 10.2 (38f0a0, 10 trials) is blocked at the IP level, so the
		// backstop rejects even a valid password before the method runs
		$this->app->visitor()->ip('10.2.123.234');

		$methods = $this->app->auth()->methods();

		$this->expectException(RateLimitException::class);
		$methods->authenticate('password', 'marge@simpsons.com', 'secret123');
	}

	public function testAuthenticateNotBlockedBelowLimit(): void
	{
		$accounts = static::TMP . '/site/accounts';
		Dir::make($accounts);
		copy(static::FIXTURES . '/logins.json', $accounts . '/.logins');

		// IP 10.1 (87084f, 9 trials) is below the limit, so the backstop
		// lets the attempt through and authentication still succeeds
		$this->app->visitor()->ip('10.1.123.234');

		$methods = $this->app->auth()->methods();
		$result  = $methods->authenticate('password', 'marge@simpsons.com', 'secret123');

		$this->assertInstanceOf(User::class, $result);
		$this->assertSame('marge', $result->id());
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

	public function testClear(): void
	{
		$session = $this->app->session();
		$session->set('kirby.method.data', 'nonce');

		$this->app->auth()->methods()->clear($session);

		$this->assertNull($session->get('kirby.method.data'));
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

	public function testEnabledConstructorException(): void
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['broken', 'password']
				]
			]
		]);

		$methods = $app->auth()->methods();
		$this->assertSame(['password' => []], $methods->enabled());
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

	public function testEnabledPerType(): void
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['oauth-gitlab', 'oauth-github', 'password']
				]
			]
		]);

		$methods = $app->auth()->methods();

		$this->assertSame([
			'oauth-github' => [],
			'password'     => []
		], $methods->enabled());

		// the login view redirects to the type of the first method,
		// so it must be the type it is registered with
		$first = $methods->firstEnabled();
		$this->assertSame('oauth-github', $first->type());
		$this->assertTrue($methods->has($first->type()));
	}

	public function testEnabledUnknownType(): void
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['pasword', 'password']
				]
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('No auth method class for: pasword');

		$app->auth()->methods()->enabled();
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

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('No auth method is enabled');

		$methods->firstEnabled();
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
		$this->assertSame('code', $methods->get('code')->type());
	}

	public function testGetWithType(): void
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => [
						'oauth-github' => ['client' => 'a'],
						'oauth-gitlab' => ['client' => 'b']
					]
				]
			]
		]);

		$methods = $app->auth()->methods();
		$github  = $methods->get('oauth-github');
		$gitlab  = $methods->get('oauth-gitlab');

		$this->assertInstanceOf(ProviderMethod::class, $github);
		$this->assertSame('oauth-github', $github->type());
		$this->assertSame(['client' => 'a'], $github->options());

		// options are passed even if the method is not enabled
		$this->assertInstanceOf(ProviderMethod::class, $gitlab);
		$this->assertSame('oauth-gitlab', $gitlab->type());
		$this->assertSame(['client' => 'b'], $gitlab->options());
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
