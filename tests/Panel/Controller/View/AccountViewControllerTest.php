<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\Ui\Button\ViewButtons;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelViewController::class)]
#[CoversClass(AccountViewController::class)]
class AccountViewControllerTest extends UserViewControllerTest
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.AccountViewController';

	public function setUp(): void
	{
		parent::setUp();
		$this->app->impersonate('test');
	}

	public function testButtons(): void
	{
		$controller = new AccountViewController($this->user);
		$buttons    = $controller->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
		$this->assertCount(2, $buttons->render());
	}

	public function testBreadcrumb(): void
	{
		$controller = new AccountViewController($this->user);
		$breadcrumb = $controller->breadcrumb();
		$this->assertSame('Test User', $breadcrumb[0]['label']);
		$this->assertSame('/account', $breadcrumb[0]['link']);
	}

	public function testComponent(): void
	{
		$controller = new AccountViewController($this->user);
		$this->assertSame('k-account-view', $controller->component());
	}
}
