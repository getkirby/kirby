<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SearchViewController::class)]
class SearchViewControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.SearchViewController';

	public function testLoad(): void
	{
		$controller = new SearchViewController();
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-search-view', $view->component);

		$props = $view->props();
		$this->assertNull($props['type']);
	}

	public function testLoadWithType(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'type' => 'files'
				]
			]
		]);

		$controller = new SearchViewController();
		$props      = $controller->load()->props();
		$this->assertSame('files', $props['type']);
	}
}
