<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Drawer;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LabDocDrawerController::class)]
class LabDocDrawerControllerTest extends TestCase
{
	public function testLoad(): void
	{
		$controller = new LabDocDrawerController('k-box');
		$drawer     = $controller->load();
		$this->assertInstanceOf(Drawer::class, $drawer);
		$this->assertSame('k-lab-docs-drawer', $drawer->component);
		$this->assertSame('book', $drawer->icon);
		$this->assertSame('k-box', $drawer->title);
	}
}
