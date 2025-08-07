<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LabExamplesViewController::class)]
class LabExamplesViewControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.LabExamplesViewController';

	public function testInfo(): void
	{
		$controller = new LabExamplesViewController();
		$info       = $controller->info();
		$this->assertNull($info);
	}

	public function testLoad(): void
	{
		$controller = new LabExamplesViewController();
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-lab-index-view', $view->component);

		$props = $view->props();
		$this->assertSame('examples', $props['tab']);
		$this->assertIsArray($props['categories']);
	}
}
