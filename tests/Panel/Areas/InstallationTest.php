<?php

namespace Kirby\Panel\Areas;

class InstallationTest extends AreaTestCase
{
	public function testInstallationRedirectFromHome(): void
	{
		$this->assertRedirect('/', 'installation');
	}

	public function testInstallationRedirectFromAnywhere(): void
	{
		$this->assertRedirect('somewhere', 'installation');
	}

	public function testInstallationWhenInstalled(): void
	{
		$this->install();
		$this->assertRedirect('installation', 'login');
	}

	public function testInstallationWhenAuthenticated(): void
	{
		$this->install();
		$this->login();
		$this->assertRedirect('installation', 'site');
	}
}
