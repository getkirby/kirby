<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Auth;
use Kirby\Auth\Method;
use Kirby\Auth\Methods;
use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Http\Request;
use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;

#[CoversClass(Method::class)]
#[CoversClass(BasicAuthMethod::class)]
class BasicAuthMethodTest extends TestCase
{
	protected function request(bool $isSsl, object|null $header): Request
	{
		return new class ($isSsl, $header) extends Request {
			public function __construct(
				protected bool $ssl,
				protected object|null $header
			) {
			}

			public function ssl(): bool
			{
				return $this->ssl;
			}

			public function auth(): \Kirby\Http\Request\Auth|false|null
			{
				return $this->header;
			}
		};
	}

	protected function auth(
		bool $auth = true,
		bool $isSsl = true,
		bool $allowInsecure = false,
		bool $hasPassword = true,
		bool $hasAnyWith2FA = false,
		object|null $header = null
	): Auth&Stub {
		$kirby = $this->createConfiguredStub(App::class, [
			'request' => $this->request($isSsl, $header),
		]);
		$kirby->method('option')->willReturnCallback(
			fn (string $key) => match ($key) {
				'api.basicAuth'     => $auth,
				'api.allowInsecure' => $allowInsecure,
				default             => null
			}
		);

		$config = $hasPassword ? ['password' => []] : [];

		if ($hasAnyWith2FA && $hasPassword) {
			$config['password']['2fa'] = true;
		}

		$methods = $this->createConfiguredStub(Methods::class, [
			'config'      => $config,
			'requires2FA' => $hasAnyWith2FA,
		]);

		return $this->createConfiguredStub(Auth::class, [
			'kirby'   => $kirby,
			'methods' => $methods,
		]);
	}

	protected function header(
		string $user = 'kirby',
		string $password = 'secret'
	): BasicAuth {
		return $this->createConfiguredStub(BasicAuth::class, [
			'username' => $user,
			'password' => $password,
		]);
	}

	public function testAuthenticate(): void
	{
		$user = $this->createStub(User::class);
		$auth = $this->auth(header: $this->header());
		$auth->method('validatePassword')
			->willReturnCallback(function (...$args) use ($user) {
				$this->assertSame(['kirby@getkirby.com', 'topsecret'], $args);
				return $user;
			});

		$method = new BasicAuthMethod(auth: $auth);
		$result = $method->authenticate('kirby@getkirby.com', 'topsecret');
		$this->assertSame($user, $result);
	}

	public function testAuthenticateWithoutPassword(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Missing password');

		$auth   = $this->auth();
		$method = new BasicAuthMethod(auth: $auth);
		$method->authenticate('kirby@getkirby.com');
	}

	public function testIsEnabled(): void
	{
		$header = $this->header();
		$auth   = $this->auth(header: $header);
		$this->assertTrue(BasicAuthMethod::isEnabled($auth));
	}

	public function testIsEnabledDisabled(): void
	{
		$auth = $this->auth(auth: false, header: $this->header());
		$this->assertFalse(BasicAuthMethod::isEnabled($auth));

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Basic authentication is not activated');
		BasicAuthMethod::isEnabled($auth, fail: true);
	}

	public function testIsEnabledInvalidHeader(): void
	{
		$auth = $this->auth(header: null);
		$this->assertFalse(BasicAuthMethod::isEnabled($auth));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid authorization header');
		BasicAuthMethod::isEnabled($auth, fail: true);
	}

	public function testIsEnabledWithoutPasswordLogin(): void
	{
		$auth = $this->auth(hasPassword: false, header: $this->header());
		$this->assertFalse(BasicAuthMethod::isEnabled($auth));

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Login with password is not enabled');
		BasicAuthMethod::isEnabled($auth, fail: true);
	}

	public function testIsEnabledWith2FA(): void
	{
		$auth = $this->auth(hasAnyWith2FA: true, header: $this->header());
		$this->assertFalse(BasicAuthMethod::isEnabled($auth));

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Basic authentication cannot be used with 2FA');
		BasicAuthMethod::isEnabled($auth, fail: true);
	}

	public function testIsEnabledInsecure(): void
	{
		$auth = $this->auth(isSsl: false, allowInsecure: false, header: $this->header());
		$this->assertFalse(BasicAuthMethod::isEnabled($auth));

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Basic authentication is only allowed over HTTPS');
		BasicAuthMethod::isEnabled($auth, fail: true);
	}

	public function testIsEnabledInsecureAllowed(): void
	{
		$auth = $this->auth(isSsl: false, allowInsecure: true, header: $this->header());
		$this->assertTrue(BasicAuthMethod::isEnabled($auth));
	}

	public function testUser(): void
	{
		$user   = $this->createStub(User::class);
		$header = $this->header('homer', 'donut');
		$auth   = $this->auth(header: $header);
		$auth->method('validatePassword')
			->willReturnCallback(function (...$args) use ($user) {
				$this->assertSame(['homer', 'donut'], $args);
				return $user;
			});

		$method = new BasicAuthMethod(auth: $auth);
		$this->assertSame($user, $method->user($header));
	}
}
