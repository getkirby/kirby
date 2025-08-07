<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LoginViewController::class)]
class LoginViewControllerTest extends TestCase
{
	public function testLoad(): void
	{
		$controller = new LoginViewController();
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-login-view', $view->component);

		$props = $view->props();
		$this->assertSame(['password'], $props['methods']);
		$this->assertNull($props['pending']['email']);
		$this->assertNull($props['pending']['challenge']);
	}

	public function testMethods(): void
	{
		$controller = new LoginViewController();
		$methods    = $controller->methods();
		$this->assertSame(['password'], $methods);
	}

	public function testPending(): void
	{
		$controller = new LoginViewController();
		$pending    = $controller->pending();
		$this->assertNull($pending['email']);
		$this->assertNull($pending['challenge']);
	}
}
