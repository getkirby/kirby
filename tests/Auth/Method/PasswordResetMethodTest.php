<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Auth;
use Kirby\Auth\Method;
use Kirby\Auth\Methods;
use Kirby\Auth\Status;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Ui\Component;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Method::class)]
#[CoversClass(PasswordResetMethod::class)]
class PasswordResetMethodTest extends TestCase
{
	protected function auth(bool $has2fa = false): Auth
	{
		$methods = $this->createStub(Methods::class);
		$config = ['password' => []];

		if ($has2fa === true) {
			$config['password']['2fa'] = true;
		}

		$methods->method('config')->willReturn($config);
		$methods->method('hasAnyRequiring2FA')->willReturn($has2fa === true);

		$auth = $this->createStub(Auth::class);
		$auth->method('methods')->willReturn($methods);

		return $auth;
	}

	public function testAuthenticate(): void
	{
		$args   = null;
		$status = $this->createStub(Status::class);
		$auth   = $this->createStub(Auth::class);
		$auth->method('createChallenge')
			->willReturnCallback(function (...$x) use (&$args, $status) {
				$args = $x;
				return $status;
			});

		$method = new PasswordResetMethod(auth: $auth, type: 'password-reset');

		$result = $method->authenticate('marge@simpsons.com');
		$this->assertInstanceOf(Status::class, $result);
		$this->assertSame($status, $result);
		$this->assertSame(['marge@simpsons.com', false, 'password-reset'], $args);

		// Password reset should ignore `long: true`
		$result = $method->authenticate('lisa@simpsons.com', long: true);
		$this->assertSame(['lisa@simpsons.com', false, 'password-reset'], $args);
	}

	public function testForm(): void
	{
		$auth   = $this->auth();
		$method = new PasswordResetMethod(auth: $auth, type: 'password-reset');
		$form   = $method->form();

		$this->assertInstanceOf(Component::class, $form);

		$rendered = $form->render();
		$this->assertSame('k-login-password-reset-method-form', $rendered['component']);
		$this->assertSame('question', $rendered['props']['submit']['icon']);
		$this->assertSame('Forgot password', $rendered['props']['submit']['label']);
	}

	public function testIcon(): void
	{
		$icon = (new PasswordResetMethod(auth: $this->auth(), type: 'password-reset'))->icon();
		$this->assertSame('question', $icon);
	}

	public function testIsEnabled(): void
	{
		$auth = $this->auth();
		$this->assertTrue((new PasswordResetMethod(auth: $auth, type: 'password-reset'))->isEnabled());
	}

	public function testIsEnabledWith2FA(): void
	{
		$auth = $this->auth(has2fa: true);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "password-reset" login method cannot be enabled when 2FA is required');

		(new PasswordResetMethod(auth: $auth, type: 'password-reset'))->isEnabled();
	}

	public function testSettings(): void
	{
		$user     = $this->createStub(User::class);
		$settings = (new PasswordResetMethod(auth: $this->auth(), type: 'password-reset'))->settings($user);
		$this->assertCount(0, $settings);
	}
}
