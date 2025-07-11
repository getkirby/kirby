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

	public function testInstallation(): void
	{
		$view = $this->view('installation');

		$this->assertSame('Installation', $view['title']);
		$this->assertSame('k-installation-view', $view['component']);

		$this->assertFalse($view['props']['isInstallable']);
		$this->assertFalse($view['props']['isInstalled']);
		$this->assertTrue($view['props']['isOk']);

		$requirements = [
			'accounts' => true,
			'content'  => true,
			'curl'     => true,
			'sessions' => true,
			'mbstring' => true,
			'media'    => true,
			'php'      => true,
		];

		$this->assertSame($requirements, $view['props']['requirements']);

		// check for a valid translation array
		$this->assertArrayHasKey('text', $view['props']['translations'][0]);
		$this->assertArrayHasKey('value', $view['props']['translations'][0]);
	}

	public function testInstallationWhenReady(): void
	{
		$this->installable();

		$view = $this->view('installation');

		$this->assertTrue($view['props']['isInstallable']);
		$this->assertFalse($view['props']['isInstalled']);
		$this->assertTrue($view['props']['isOk']);
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
