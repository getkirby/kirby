<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LabDocsViewController::class)]
class LabDocsViewControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.LabDocsViewController';

	public function testBreadcrumb(): void
	{
		$controller = new LabDocsViewController();
		$breadcrumb = $controller->breadcrumb();
		$this->assertSame('Docs', $breadcrumb[0]['label']);
		$this->assertSame('lab/docs', $breadcrumb[0]['link']);
	}

	public function testInfo(): void
	{
		$controller = new LabDocsViewController();
		$info       = $controller->info();
		$this->assertNull($info);
	}

	public function testLoad(): void
	{
		$controller = new LabDocsViewController();
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-lab-index-view', $view->component);

		$props = $view->props();
		$this->assertSame('docs', $props['tab']);
		$this->assertSame('Docs', $props['title']);
		$this->assertArrayHasKey('examples', $props['categories'][0]);
	}
}
