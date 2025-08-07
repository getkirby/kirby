<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LabDocViewController::class)]
class LabDocViewControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.LabDocViewController';

	public function testBreadcrumb(): void
	{
		$controller = new LabDocViewController('k-box');
		$breadcrumb = $controller->breadcrumb();
		$this->assertSame('Docs', $breadcrumb[0]['label']);
		$this->assertSame('lab/docs', $breadcrumb[0]['link']);
		$this->assertSame('k-box', $breadcrumb[1]['label']);
		$this->assertSame('lab/docs/k-box', $breadcrumb[1]['link']);
	}

	public function testButtons(): void
	{
		$controller = new LabDocViewController('k-box');
		$buttons = $controller->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
		$this->assertCount(2, $buttons->render());

		// without a Lab example
		$controller = new LabDocViewController('k-topbar');
		$buttons = $controller->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
		$this->assertCount(1, $buttons->render());
	}

	public function testLoad(): void
	{
		$controller = new LabDocViewController('k-box');
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-lab-docs-view', $view->component);

		$props = $view->props();
		$this->assertSame('k-box', $props['title']);
		$this->assertSame('/components/boxes/', $props['lab']);
		$this->assertIsArray($props['docs']);
	}
}
