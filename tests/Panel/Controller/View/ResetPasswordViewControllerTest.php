<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ResetPasswordViewController::class)]
class ResetPasswordViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.ResetPasswordViewController';

	public function testLoad(): void
	{
		$controller = new ResetPasswordViewController();
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-reset-password-view', $view->component);

		$props = $view->props();
		$this->assertTrue($props['requirePassword']);
		$this->assertSame([
			[
				'label' => 'Reset password'
			]
		], $view->breadcrumb);
	}

	public function testLoadWithResetMode(): void
	{
		$this->app->session()->set('kirby.resetPassword', true);

		$controller = new ResetPasswordViewController();
		$props      = $controller->load()->props();
		$this->assertFalse($props['requirePassword']);
	}
}
