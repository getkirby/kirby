<?php

namespace Kirby\Panel\Controller\View;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelViewController::class)]
#[CoversClass(AccountViewController::class)]
class AccountViewControllerTest extends UserViewControllerTest
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.AccountViewController';

	public function testComponent(): void
	{
		$controller = new AccountViewController($this->user);
		$this->assertSame('k-account-view', $controller->component());
	}
}
