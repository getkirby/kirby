<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Tabs::class)]
class TabsTest extends TestCase
{
	protected ModelWithContent $model;

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->model = new Page(['slug' => 'a']);
	}

	protected function tabs(array $tabs = []): Tabs
	{
		return new Tabs($tabs, $this->model);
	}

	public function testConstructFromNormalizedProps(): void
	{
		$tabs = $this->tabs([
			'content' => [
				'columns' => [],
				'icon'    => 'text',
				'label'   => 'Content',
				'name'    => 'content'
			]
		]);

		$this->assertCount(1, $tabs);
		$this->assertInstanceOf(Tab::class, $tabs->get('content'));
		$this->assertSame('text', $tabs->get('content')->icon());
		$this->assertSame('Content', $tabs->get('content')->label());
	}

	public function testConstructFromTabObjects(): void
	{
		$tab  = new Tab(model: $this->model, name: 'content');
		$tabs = $this->tabs(['content' => $tab]);

		$this->assertSame($tab, $tabs->get('content'));
	}

	public function testConstructWithoutModel(): void
	{
		$tabs = new Tabs([
			'content' => []
		]);

		$this->assertSame($this->app->site(), $tabs->model());
		$this->assertSame('/site/?tab=content', $tabs->get('content')->link());
	}

	public function testEmpty(): void
	{
		$tabs = $this->tabs();

		$this->assertCount(0, $tabs);
		$this->assertNull($tabs->first());
		$this->assertSame([], $tabs->toArray());
		$this->assertSame([], $tabs->toButtonsProps());
	}

	public function testFirst(): void
	{
		$tabs = $this->tabs([
			'content'  => [],
			'settings' => []
		]);

		$this->assertSame('content', $tabs->first()->name());
	}

	public function testGetCaseInsensitive(): void
	{
		$tabs = $this->tabs([
			'contentTab' => []
		]);

		$this->assertSame($tabs->get('contentTab'), $tabs->get('contenttab'));

		// the tab keeps its authored name for the link
		$this->assertSame('contentTab', $tabs->get('contenttab')->name());
		$this->assertSame(
			'/pages/a/?tab=contentTab',
			$tabs->get('contenttab')->link()
		);
	}

	public function testGetMissing(): void
	{
		$this->assertNull($this->tabs()->get('does-not-exist'));
	}

	public function testModel(): void
	{
		$this->assertSame($this->model, $this->tabs()->model());
	}

	public function testNameFromKey(): void
	{
		$tabs = $this->tabs([
			'content' => ['label' => 'Content']
		]);

		$this->assertSame('content', $tabs->get('content')->name());
	}

	public function testToArray(): void
	{
		$tabs = $this->tabs([
			'content'  => ['icon' => 'text'],
			'settings' => []
		]);

		$this->assertSame([
			'content' => [
				'columns' => [],
				'icon'    => 'text',
				'label'   => 'Content',
				'link'    => '/pages/a/?tab=content',
				'name'    => 'content'
			],
			'settings' => [
				'columns' => [],
				'icon'    => null,
				'label'   => 'Settings',
				'link'    => '/pages/a/?tab=settings',
				'name'    => 'settings'
			]
		], $tabs->toArray());
	}

	public function testToArrayWithMap(): void
	{
		$tabs = $this->tabs([
			'content'  => [],
			'settings' => []
		]);

		$this->assertSame(
			['content' => 'content', 'settings' => 'settings'],
			$tabs->toArray(fn ($tab) => $tab->name())
		);
	}

	public function testToButtonsProps(): void
	{
		$tabs = $this->tabs([
			'content' => [
				'columns' => [
					[
						'width'  => '1/1',
						'fields' => [
							'title' => ['type' => 'text']
						]
					]
				],
				'icon' => 'text'
			],
			'settings' => []
		]);

		$this->assertSame([
			[
				'fields' => ['title'],
				'icon'   => 'text',
				'label'  => 'Content',
				'link'   => '/pages/a/?tab=content',
				'name'   => 'content'
			],
			[
				'fields' => [],
				'icon'   => null,
				'label'  => 'Settings',
				'link'   => '/pages/a/?tab=settings',
				'name'   => 'settings'
			]
		], $tabs->toButtonsProps());
	}
}
