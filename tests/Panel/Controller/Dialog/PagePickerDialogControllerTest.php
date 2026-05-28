<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\ModelPermissions;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Exception\PermissionException;
use Kirby\Panel\Collector\PagesCollector;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelPickerDialogController::class)]
#[CoversClass(PagePickerDialogController::class)]
class PagePickerDialogControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.PagePickerDialogController';

	protected function setUp(): void
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

		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$this->assertSame(5, $controller->page);
		$this->assertSame('test', $controller->search);
	}

	public function testCollector(): void
	{
		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$this->assertInstanceOf(PagesCollector::class, $controller->collector());
	}

	public function testCollectorWithQueryForSinglePage(): void
	{
		$controller = new PagePickerDialogController(
			model: $this->app->site(),
			query: 'page("gamma")'
		);

		$this->assertInstanceOf(PagesCollector::class, $controller->collector());
		$this->assertCount(2, $controller->collector()->models());
	}

	public function testFind(): void
	{
		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$this->assertInstanceOf(Page::class, $controller->find('gamma'));
	}

	public function testItem(): void
	{
		$controller = new PagePickerDialogController(
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
		$controller = new PagePickerDialogController(
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
		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$dialog = $controller->load();
		$this->assertInstanceOf(Dialog::class, $dialog);
		$this->assertSame('k-page-picker-dialog', $dialog->component);
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

		$this->app->impersonate('kirby');

		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$parent = $controller->props()['parent'];
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

		$this->app->impersonate('kirby');

		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$parent = $controller->props()['parent'];
		$this->assertSame('gamma/epsilon', $parent['id']);
		$this->assertSame('gamma', $parent['parent']);

		// parent itself is the root page
		$controller = new PagePickerDialogController(
			model: $this->app->site(),
			query: 'page("gamma/epsilon")'
		);

		$parent = $controller->props()['parent'];
		$this->assertNull($parent['id']);
		$this->assertNull($parent['parent']);
		$this->assertSame('epsilon', $parent['title']);

		// no subpages
		$controller = new PagePickerDialogController(
			model: $this->app->site(),
			subpages: false
		);

		$parent = $controller->props()['parent'];
		$this->assertNull($parent);

		// invalid parent falls back to the root
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'parent' => 'foo',
				],
			],
		]);

		$this->app->impersonate('kirby');

		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$parent = $controller->props()['parent'];
		$this->assertNull($parent['id']);
		$this->assertNull($parent['parent']);
	}

	public function testParentAccessibleButNotListable(): void
	{
		ModelPermissions::$cache = [];

		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/limited' => [
					'options' => [
						'list' => false
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'visible-page',
						'template' => 'limited',
						'children' => [
							['slug' => 'visible-sub', 'num' => 1]
						]
					]
				]
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			],
			'request' => [
				'query' => [
					'parent' => 'visible-page',
				],
			],
		]);

		$this->app->impersonate('editor');

		$page = $this->app->page('visible-page');
		$this->assertTrue($page->isAccessible());
		$this->assertFalse($page->isListable());

		// accessible-but-not-listable pages are valid picker parents
		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$this->assertSame($page, $controller->parent());
		$this->assertSame('visible-page', $controller->props()['parent']['id']);

		// the picker navigates into the accessible page, listing its
		// children rather than the (filtered) site children
		$this->assertSame(
			['visible-page/visible-sub'],
			array_column($controller->items(), 'id')
		);
	}

	public function testParentNotAccessibleFallsBackToRoot(): void
	{
		ModelPermissions::$cache = [];

		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/forbidden' => [
					'options' => [
						'access' => false
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'hidden-page',
						'template' => 'forbidden'
					]
				]
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			],
			'request' => [
				'query' => [
					'parent' => 'hidden-page',
				],
			],
		]);

		$this->app->impersonate('editor');

		$page = $this->app->page('hidden-page');
		$this->assertFalse($page->isAccessible());
		$this->assertTrue($this->app->site()->isAccessible());

		// inaccessible parents must fall back to the root
		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$this->assertInstanceOf(Site::class, $controller->parent());
		$this->assertNull($controller->props()['parent']['id']);

		// falls back to listing the root's (listable) children rather
		// than navigating into the inaccessible page
		$root = $this->app->site()->children()->filter('isListable', true);
		$this->assertSame(
			$root->values(fn (Page $model) => $model->id()),
			array_column($controller->items(), 'id')
		);
	}

	public function testParentAndRootNotAccessibleThrows(): void
	{
		ModelPermissions::$cache = [];

		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/forbidden' => [
					'options' => [
						'access' => false
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'hidden-page',
						'template' => 'forbidden'
					]
				]
			],
			'roles' => [
				['name' => 'admin'],
				[
					'name' => 'editor',
					'permissions' => [
						'site' => [
							'access' => false
						]
					]
				]
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			],
			'request' => [
				'query' => [
					'parent' => 'hidden-page',
				],
			],
		]);

		$this->app->impersonate('editor');

		$this->assertFalse($this->app->page('hidden-page')->isAccessible());
		$this->assertFalse($this->app->site()->isAccessible());

		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$this->expectException(PermissionException::class);
		$controller->parent();
	}

	public function testProps(): void
	{
		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$props = $controller->props();
		$this->assertSame('k-page-picker-dialog', $props['component']);
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

		$this->app->impersonate('kirby');

		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$props = $controller->props();
		$this->assertSame(['alpha', 'beta'], $props['value']);
	}

	public function testItemsWithParentNavigation(): void
	{
		// default: lists the site's children
		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$this->assertSame(
			['alpha', 'beta', 'gamma'],
			array_column($controller->items(), 'id')
		);

		// navigating into a parent lists that parent's children
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'parent' => 'gamma',
				],
			],
		]);

		$this->app->impersonate('kirby');

		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$this->assertSame(
			['gamma/delta', 'gamma/epsilon'],
			array_column($controller->items(), 'id')
		);
	}

	public function testRoot(): void
	{
		$controller = new PagePickerDialogController(
			model: $this->app->site()
		);

		$this->assertInstanceOf(Site::class, $controller->root());

		$controller = new PagePickerDialogController(
			model: $this->app->page('gamma'),
			query: 'page("gamma").children'
		);

		$this->assertInstanceOf(Page::class, $controller->root());
		$this->assertSame('gamma', $controller->root()->id());

		$controller = new PagePickerDialogController(
			model: $this->app->page('gamma'),
			query: 'page("gamma")'
		);

		$this->assertInstanceOf(Page::class, $controller->root());
		$this->assertSame('gamma', $controller->root()->id());

		$controller = new PagePickerDialogController(
			model: $this->app->page('gamma'),
			query: 'foo'
		);

		$this->assertInstanceOf(Site::class, $controller->root());
	}
}
