<?php

namespace Kirby\Panel\Areas;

class AccountTest extends AreaTestCase
{
	public function testAccountWithoutInstallation(): void
	{
		$this->assertRedirect('account', 'installation');
	}

	public function testAccountWithoutAuthentication(): void
	{
		$this->install();
		$this->assertRedirect('account', 'login');
	}
}
