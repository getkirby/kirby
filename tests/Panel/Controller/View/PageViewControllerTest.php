<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Page;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelViewController::class)]
#[CoversClass(PageViewController::class)]
class PageViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.PageViewController';

	protected Page $page;

	public function setUp(): void
	{
		parent::setUp();
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'children' => [
							[
								'slug' => 'b',
								'children' => [
									['slug' => 'c'],
								]
							],
						]
					],
				]
			]
		]);

		$this->app->impersonate('kirby');
		$this->page = $this->app->page('a');
	}

	public function testBreadcrumb(): void
	{
		$controller = new PageViewController($this->app->page('a'));
		$breadcrumb = $controller->breadcrumb();
		$this->assertSame([
			[
				'label' => 'a',
				'link'  => '/pages/a'
			]
		], $breadcrumb);

		$controller = new PageViewController($this->app->page('a/b/c'));
		$breadcrumb = $controller->breadcrumb();
		$this->assertSame([
			[
				'label' => 'a',
				'link'  => '/pages/a'
			],
			[
				'label' => 'b',
				'link'  => '/pages/a+b'
			],
			[
				'label' => 'c',
				'link'  => '/pages/a+b+c'
			]
		], $breadcrumb);
	}

	public function testButtons(): void
	{
		$controller = new PageViewController($this->page);
		$buttons    = $controller->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
		$this->assertCount(5, $buttons->render());
	}

	public function testComponent(): void
	{
		$controller = new PageViewController($this->page);
		$this->assertSame('k-page-view', $controller->component());
	}

	public function testFactory(): void
	{
		$controller = PageViewController::factory('a');
		$this->assertInstanceOf(PageViewController::class, $controller);
		$this->assertSame($this->app->page('a'), $controller->model());
	}

	public function testLoad(): void
	{
		$controller = new PageViewController($this->page);
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-page-view', $view->component);

		$props = $view->props();
		$this->assertArrayHasKey('id', $props);
		$this->assertArrayHasKey('title', $props);

		// inherited props
		$this->assertArrayHasKey('blueprint', $props);
		$this->assertArrayHasKey('lock', $props);
		$this->assertArrayHasKey('permissions', $props);
		$this->assertArrayNotHasKey('tab', $props);
		$this->assertArrayHasKey('tabs', $props);
		$this->assertArrayHasKey('versions', $props);
	}

	public function testNext(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'foo'],
					['slug' => 'bar']
				]
			],
		]);

		$this->app->impersonate('kirby');

		$controller = new PageViewController($this->app->page('foo'));
		$next       = $controller->next();
		$this->assertSame('bar', $next['title']);
		$this->assertSame('/pages/bar', $next['link']);

		$controller = new PageViewController($this->app->page('bar'));
		$next       = $controller->next();
		$this->assertNull($next);

		// with tab
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'tab' => 'test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageViewController($this->app->page('foo'));
		$next       = $controller->next();
		$this->assertSame('bar', $next['title']);
		$this->assertSame('/pages/bar?tab=test', $next['link']);
	}

	public function testPrev(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'foo'],
					['slug' => 'bar']
				]
			],
		]);

		$this->app->impersonate('kirby');

		$controller = new PageViewController($this->app->page('foo'));
		$prev       = $controller->prev();
		$this->assertNull($prev);

		$controller = new PageViewController($this->app->page('bar'));
		$prev       = $controller->prev();
		$this->assertSame('foo', $prev['title']);
		$this->assertSame('/pages/foo', $prev['link']);

		// with tab
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'tab' => 'test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PageViewController($this->app->page('bar'));
		$prev       = $controller->prev();
		$this->assertSame('foo', $prev['title']);
		$this->assertSame('/pages/foo?tab=test', $prev['link']);
	}

	public function testPrevNextSameTemplate(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'foo', 'template' => 'note'],
					['slug' => 'bar', 'template' => 'album'],
					['slug' => 'baz', 'template' => 'note']
				]
			],
		]);

		$this->app->impersonate('kirby');

		$controller = new PageViewController($this->app->page('foo'));
		$this->assertSame('/pages/baz', $controller->next()['link']);

		$controller = new PageViewController($this->app->page('bar'));
		$this->assertNull($controller->prev());
		$this->assertNull($controller->next());

		$controller = new PageViewController($this->app->page('baz'));
		$this->assertSame('/pages/foo', $controller->prev()['link']);
	}

	public function testPrevNextSameStatus(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'foo', 'num' => 0],
					['slug' => 'bar', 'num' => null],
					['slug' => 'baz', 'num' => 0]
				]
			],
		]);

		$this->app->impersonate('kirby');

		$controller = new PageViewController($this->app->page('foo'));
		$this->assertSame('/pages/baz', $controller->next()['link']);

		$controller = new PageViewController($this->app->page('bar'));
		$this->assertNull($controller->prev());
		$this->assertNull($controller->next());

		$controller = new PageViewController($this->app->page('baz'));
		$this->assertSame('/pages/foo', $controller->prev()['link']);
	}

	public function testPrevNextWithNavigationOne(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/a' => [
					'title'      => 'A',
					'navigation' => [
						'status'   => 'all',
						'template' => 'all'
					]
				],
				'pages/b' => [
					'title'      => 'B',
					'navigation' => [
						'status'   => 'all',
						'template' => 'all'
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$parent = Page::create(['slug' => 'test']);
		$parent->createChild([
			'slug'     => 'a',
			'template' => 'a'
		]);

		$expectedPrev = $parent->createChild([
			'slug'     => 'b',
			'template' => 'b'
		]);

		$parent->createChild([
			'slug'     => 'c',
			'template' => 'a'
		]);

		$expectedNext = $parent->createChild([
			'slug'     => 'd',
			'template' => 'b'
		]);

		$page       = $this->app->page('test/c');

		$navigation = $page->blueprint()->navigation();
		$this->assertSame(['status' => 'all', 'template' => 'all'], $navigation);

		$controller = new PageViewController($page);
		$this->assertNotNull($controller->prev());
		$this->assertNotNull($controller->next());

		$this->assertSame($expectedNext->panel()->toLink(), $controller->next());
		$this->assertSame($expectedPrev->panel()->toLink(), $controller->prev());
	}

	public function testPrevNextWithNavigationTwo(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/c' => [
					'title' => 'C',
					'navigation' => [
						'status' => ['listed'],
						'template' => ['c']
					]
				],
				'pages/d' => [
					'title' => 'D',
					'navigation' => [
						'status' => ['listed'],
						'template' => ['c']
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$parent = Page::create(['slug' => 'test']);

		$expectedPrev = $parent->createChild([
			'slug'     => 'a',
			'template' => 'c'
		])->changeStatus('listed');

		$parent->createChild([
			'slug'     => 'b',
			'template' => 'd'
		])->changeStatus('listed');

		$parent->createChild([
			'slug'     => 'c',
			'template' => 'c'
		]);

		$parent->createChild([
			'slug'     => 'd',
			'template' => 'd'
		])->changeStatus('listed');

		$expectedNext = $parent->createChild([
			'slug'     => 'e',
			'template' => 'c'
		])->changeStatus('listed');

		$parent->createChild([
			'slug'     => 'f',
			'template' => 'd'
		])->changeStatus('listed');

		$page       = $this->app->page('test/d');
		$navigation = $page->blueprint()->navigation();
		$this->assertSame([
			'status' => ['listed'],
			'template' => ['c']
		], $navigation);


		$controller = new PageViewController($page);
		$this->assertNotNull($controller->prev());
		$this->assertNotNull($controller->next());
		$this->assertSame($expectedNext->panel()->toLink(), $controller->next());
		$this->assertSame($expectedPrev->panel()->toLink(), $controller->prev());
	}

	public function testPrevNextWithNavigationThree(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/e' => [
					'title' => 'E',
					'navigation' => [
						'status' => ['listed'],
						'template' => ['e', 'f']
					]
				],
				'pages/f' => [
					'title' => 'F',
					'navigation' => [
						'status' => ['listed'],
						'template' => ['e', 'f']
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$parent = Page::create([
			'slug' => 'test'
		]);

		$expectedPrev = $parent->createChild([
			'slug'     => 'a',
			'template' => 'e'
		])->changeStatus('listed');

		$parent->createChild([
			'slug'     => 'b',
			'template' => 'f'
		])->changeStatus('unlisted');

		$parent->createChild([
			'slug'     => 'c',
			'template' => 'e'
		])->changeStatus('unlisted');

		$parent->createChild([
			'slug'     => 'd',
			'template' => 'f'
		])->changeStatus('listed');

		$parent->createChild([
			'slug'     => 'e',
			'template' => 'e'
		])->changeStatus('unlisted');

		$expectedNext = $parent->createChild([
			'slug'     => 'f',
			'template' => 'f'
		])->changeStatus('listed');

		$page  = $this->app->page('test/d');
		$navigation = $page->blueprint()->navigation();
		$this->assertSame([
			'status' => ['listed'],
			'template' => ['e', 'f']
		], $navigation);

		$controller = new PageViewController($page);
		$this->assertNotNull($controller->prev());
		$this->assertNotNull($controller->next());
		$this->assertSame($expectedNext->panel()->toLink(), $controller->next());
		$this->assertSame($expectedPrev->panel()->toLink(), $controller->prev());
	}

	public function testPrevNextWithNavigationFour(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/g' => [
					'title' => 'A',
					'navigation' => [
						'status' => 'all',
						'template' => 'all',
						'sortBy' => 'slug desc'
					]
				],
				'pages/h' => [
					'title' => 'B',
					'navigation' => [
						'status' => 'all',
						'template' => 'all',
						'sortBy' => 'slug desc'
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$parent = Page::create([
			'slug' => 'test'
		]);

		$parent->createChild([
			'slug'     => 'a',
			'template' => 'g'
		]);

		$expectedNext = $parent->createChild([
			'slug'     => 'b',
			'template' => 'h'
		]);

		$parent->createChild([
			'slug'     => 'c',
			'template' => 'g'
		]);

		$expectedPrev = $parent->createChild([
			'slug'     => 'd',
			'template' => 'h'
		]);

		$page  = $this->app->page('test/c');
		$navigation = $page->blueprint()->navigation();
		$this->assertSame([
			'status' => 'all',
			'template' => 'all',
			'sortBy' => 'slug desc'
		], $navigation);

		$controller = new PageViewController($page);
		$this->assertNotNull($controller->prev());
		$this->assertNotNull($controller->next());
		$this->assertSame($expectedNext->panel()->toLink(), $controller->next());
		$this->assertSame($expectedPrev->panel()->toLink(), $controller->prev());
	}

	public function testTitle(): void
	{
		$controller = new PageViewController($this->page);
		$this->assertSame('a', $controller->title());
	}
}
