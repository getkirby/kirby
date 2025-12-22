<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InstallationViewController::class)]
class InstallationViewControllerTest extends TestCase
{
	public function testLoad(): void
	{
		$controller = new InstallationViewController();
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-installation-view', $view->component);

		$props = $view->props();
		$this->assertFalse($props['isInstallable']);
		$this->assertFalse($props['isInstalled']);
		$this->assertTrue($props['isOk']);

		$requirements = [
			'accounts' => true,
			'content'  => true,
			'media'    => true,
			'php'      => true,
			'sessions' => true,
			'extensions' => [
				'ctype'     => true,
				'curl'      => true,
				'dom'       => true,
				'filter'    => true,
				'hash'      => true,
				'iconv'     => true,
				'json'      => true,
				'libxml'    => true,
				'mbstring'  => true,
				'openssl'   => true,
				'SimpleXML' => true,
			]
		];

		$this->assertSame($requirements, $props['requirements']);

		// check for a valid translation array
		$this->assertArrayHasKey('text', $props['translations'][0]);
		$this->assertArrayHasKey('value', $props['translations'][0]);
	}

	public function testLoadWhenReady(): void
	{
		// fake a valid server
		$_SERVER['SERVER_SOFTWARE'] = 'php';

		// installation has to be allowed
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'install' => true
				]
			]
		]);

		$controller = new InstallationViewController();
		$props      = $controller->load()->props();
		$this->assertTrue($props['isInstallable']);
	}
}
