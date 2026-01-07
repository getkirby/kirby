<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Auth\Methods;
use Kirby\Cms\App;
use Kirby\Cms\Auth;
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
				parent::__construct(options: [
					'url' => [
						'scheme' => $ssl ? 'https' : 'http',
						'host'   => 'example.com'
					]
				]);
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
		$kirby   = $this->createStub(App::class);
		$request = $this->request($isSsl, $header);
		$kirby->method('request')->willReturn($request);
		$kirby->method('option')->willReturnCallback(
			function (string $key) use ($auth, $allowInsecure) {
				return match ($key) {
					'api.basicAuth'     => $auth,
					'api.allowInsecure' => $allowInsecure,
					default              => null
				};
			}
		);

		$methods = $this->createStub(Methods::class);
		$methods->method('has')->willReturn($hasPassword);
		$methods->method('hasAnyWith2FA')->willReturn($hasAnyWith2FA);

		$auth = $this->createStub(Auth::class);
		$auth->method('kirby')->willReturn($kirby);
		$auth->method('methods')->willReturn($methods);

		return $auth;
	}

	protected function header(
		string $user = 'kirby',
		string $password = 'secret'
	): BasicAuth {
		$header = $this->createStub(BasicAuth::class);
		$header->method('username')->willReturn($user);
		$header->method('password')->willReturn($password);

		return $header;
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

	public function testIsAvailable(): void
	{
		$header = $this->header();
		$auth   = $this->auth(header: $header);
		$this->assertTrue(BasicAuthMethod::isAvailable($auth));
	}

	public function testIsAvailableDisabled(): void
	{
		$auth = $this->auth(auth: false, header: $this->header());
		$this->assertFalse(BasicAuthMethod::isAvailable($auth));

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Basic authentication is not activated');
		BasicAuthMethod::isAvailable($auth, fail: true);
	}

	public function testIsAvailableInvalidHeader(): void
	{
		$auth = $this->auth(header: null);
		$this->assertFalse(BasicAuthMethod::isAvailable($auth));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid authorization header');
		BasicAuthMethod::isAvailable($auth, fail: true);
	}

	public function testIsAvailableWithoutPasswordLogin(): void
	{
		$auth = $this->auth(hasPassword: false, header: $this->header());
		$this->assertFalse(BasicAuthMethod::isAvailable($auth));

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Login with password is not enabled');
		BasicAuthMethod::isAvailable($auth, fail: true);
	}

	public function testIsAvailableWith2FA(): void
	{
		$auth = $this->auth(hasAnyWith2FA: true, header: $this->header());
		$this->assertFalse(BasicAuthMethod::isAvailable($auth));

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Basic authentication cannot be used with 2FA');
		BasicAuthMethod::isAvailable($auth, fail: true);
	}

	public function testIsAvailableInsecure(): void
	{
		$auth = $this->auth(isSsl: false, allowInsecure: false, header: $this->header());
		$this->assertFalse(BasicAuthMethod::isAvailable($auth));

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Basic authentication is only allowed over HTTPS');
		BasicAuthMethod::isAvailable($auth, fail: true);
	}

	public function testIsAvailableInsecureAllowed(): void
	{
		$auth = $this->auth(isSsl: false, allowInsecure: true, header: $this->header());
		$this->assertTrue(BasicAuthMethod::isAvailable($auth));
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
