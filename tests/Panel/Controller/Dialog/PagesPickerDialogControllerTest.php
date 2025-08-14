<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Panel\Collector\PagesCollector;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelsPickerDialogController::class)]
#[CoversClass(PagesPickerDialogController::class)]
class PagesPickerDialogControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.PagesPickerDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'alpha'],
					['slug' => 'beta'],
					[
						'slug' => 'gamma',
						'children' => [
							['slug' => 'delta'],
							['slug' => 'epsilon'],
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function test__Construct(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'page'   => 5,
					'search' => 'test',
				],
			],
		]);

		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$this->assertSame(5, $controller->page);
		$this->assertSame('test', $controller->search);
	}

	public function testCollector(): void
	{
		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$this->assertInstanceOf(PagesCollector::class, $controller->collector());
	}

	public function testCollectorWithQueryForSinglePage(): void
	{
		$controller = new PagesPickerDialogController(
			model: $this->app->site(),
			query: 'page("gamma")'
		);

		$this->assertInstanceOf(PagesCollector::class, $controller->collector());
		$this->assertCount(2, $controller->collector()->models());
	}

	public function testFind(): void
	{
		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$this->assertInstanceOf(Page::class, $controller->find('gamma'));
	}

	public function testItem(): void
	{
		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$item = $controller->item($this->app->page('alpha'));
		$this->assertArrayHasKey('image', $item);
		$this->assertSame('', $item['info']);
		$this->assertSame('list', $item['layout']);
		$this->assertSame('alpha', $item['id']);
		$this->assertSame('/pages/alpha', $item['link']);
		$this->assertArrayHasKey('permissions', $item);
		$this->assertFalse($item['hasChildren']);
	}

	public function testItems(): void
	{
		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$items = $controller->items();
		$this->assertCount(3, $items);
		$this->assertSame('alpha', $items[0]['id']);
		$this->assertSame('beta', $items[1]['id']);
		$this->assertSame('gamma', $items[2]['id']);
	}

	public function testLoad(): void
	{
		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$dialog = $controller->load();
		$this->assertInstanceOf(Dialog::class, $dialog);
		$this->assertSame('k-pages-picker-dialog', $dialog->component);
	}

	public function testParent(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'parent' => 'beta',
				],
			],
		]);

		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$parent = $controller->parent();
		$this->assertSame('beta', $parent['id']);
		$this->assertNull($parent['parent']);

		// parent that has a parent
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'parent' => 'gamma/epsilon',
				],
			],
		]);

		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$parent = $controller->parent();
		$this->assertSame('gamma/epsilon', $parent['id']);
		$this->assertSame('gamma', $parent['parent']);

		// parent itself is the root page
		$controller = new PagesPickerDialogController(
			model: $this->app->site(),
			query: 'page("gamma/epsilon")'
		);

		$parent = $controller->parent();
		$this->assertNull($parent['id']);
		$this->assertNull($parent['parent']);
		$this->assertSame('epsilon', $parent['title']);

		// no subpages
		$controller = new PagesPickerDialogController(
			model: $this->app->site(),
			subpages: false
		);

		$parent = $controller->parent();
		$this->assertNull($parent);

		// invalid parent
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'parent' => 'foo',
				],
			],
		]);

		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$parent = $controller->parent();
		$this->assertNull($parent);
	}

	public function testProps(): void
	{
		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$props = $controller->props();
		$this->assertSame('k-pages-picker-dialog', $props['component']);
		$this->assertTrue($props['hasSearch']);
		$this->assertCount(3, $props['items']);
		$this->assertSame('list', $props['layout']);
		$this->assertNull($props['max']);
		$this->assertTrue($props['multiple']);
		$this->assertNull($props['size']);
		$this->assertSame([], $props['value']);
	}

	public function testPropsWithValue(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'value' => 'alpha, beta',
				],
			],
		]);

		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$props = $controller->props();
		$this->assertSame(['alpha', 'beta'], $props['value']);
	}

	public function testQuery(): void
	{
		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$this->assertSame('site.children', $controller->query());

		$controller = new PagesPickerDialogController(
			model: $this->app->page('alpha'),
			query: 'page("alpha").children'
		);

		$this->assertSame('page("alpha").children', $controller->query());

		// test with parent
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'parent' => 'beta',
				],
			],
		]);

		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$this->assertSame('page("beta").children', $controller->query());
	}

	public function testRoot(): void
	{
		$controller = new PagesPickerDialogController(
			model: $this->app->site()
		);

		$this->assertInstanceOf(Site::class, $controller->root());

		$controller = new PagesPickerDialogController(
			model: $this->app->page('gamma'),
			query: 'page("gamma").children'
		);

		$this->assertInstanceOf(Page::class, $controller->root());
		$this->assertSame('gamma', $controller->root()->id());

		$controller = new PagesPickerDialogController(
			model: $this->app->page('gamma'),
			query: 'page("gamma")'
		);

		$this->assertInstanceOf(Page::class, $controller->root());
		$this->assertSame('gamma', $controller->root()->id());

		$controller = new PagesPickerDialogController(
			model: $this->app->page('gamma'),
			query: 'foo'
		);

		$this->assertInstanceOf(Site::class, $controller->root());
	}
}
