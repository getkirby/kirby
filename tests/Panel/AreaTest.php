<?php

namespace Kirby\Panel;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Area::class)]
class AreaTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Area';

	public function testBreadcrumbLabel(): void
	{
		$area = new Area(id: 'test', label: 'Test');
		$this->assertSame('Test', $area->breadcrumbLabel());

		$area = new Area(id: 'test', breadcrumbLabel: 'Better', label: 'Test');
		$this->assertSame('Better', $area->breadcrumbLabel());
	}

	public function testDisabledDefinitions(): void
	{
		$area = new Area(
			id: 'test',
			buttons:   ['todos' => [], 'archived' => false],
			dialogs:   ['todos' => [], 'archived' => false],
			drawers:   ['todos' => [], 'archived' => false],
			dropdowns: ['todos' => [], 'archived' => false],
			requests:  ['todos' => [], 'archived' => false],
			searches:  ['todos' => [], 'archived' => false],
			views:     ['todos' => [], 'archived' => false]
		);

		$this->assertSame(['todos'], array_keys($area->buttons()));
		$this->assertSame(['todos'], array_keys($area->dialogs()));
		$this->assertSame(['todos'], array_keys($area->drawers()));
		$this->assertSame(['todos'], array_keys($area->dropdowns()));
		$this->assertSame(['todos'], array_keys($area->requests()));
		$this->assertSame(['todos'], array_keys($area->searches()));
		$this->assertSame(['todos'], array_keys($area->views()));
	}

	public function testLabel(): void
	{
		$area = new Area(id: 'test', label: 'Test');
		$this->assertSame('Test', $area->label());

		$area = new Area(id: 'test', label: ['en' => 'Tasty', 'de' => 'Lecker']);
		$this->assertSame('Tasty', $area->label());
	}

	public function testMenuItem(): void
	{
		$area = new Area(id: 'test', label: 'Test');
		$this->assertSame([
			'id'     => 'test',
			'icon'   => null,
			'label'  => 'Test',
			'link'   => null,
			'menu'   => false,
			'title'  => 'Test',
		], $area->menuItem());
	}

	public function testRoutes(): void
	{
		$area = new Area(id: 'test');
		$this->assertSame([], $area->routes());
	}

	public function testRoutesForDisabledDefinitions(): void
	{
		$area = new Area(
			id: 'test',
			dialogs:  [
				'todos'    => ['load' => fn () => []],
				'archived' => false
			],
			searches: [
				'todos'    => ['query' => fn () => []],
				'archived' => false
			]
		);

		$this->assertSame([
			'search/todos',
			'dialogs/todos',
			'dialogs/todos'
		], array_column($area->routes(), 'pattern'));
	}

	public function testRoutesForViews(): void
	{
		$area = new Area(id: 'test', views: [
			[
				'pattern' => 'todos',
				'action'  => function () {
					return [
						'component' => 'k-todos-view'
					];
				}
			]
		]);

		$routes = $area->routes();

		$this->assertCount(1, $routes);
		$this->assertSame('test', $routes[0]['area']);
		$this->assertSame('view', $routes[0]['type']);
		$this->assertSame('todos', $routes[0]['pattern']);
		$this->assertSame('k-todos-view', $routes[0]['action']()['component']);
	}

	public function testTitle(): void
	{
		$area = new Area(id: 'test', label: 'Test');
		$this->assertSame('Test', $area->title());

		$area = new Area(id: 'test', label: 'Test', title: 'Title');
		$this->assertSame('Title', $area->title());

		$area = new Area(id: 'test', title: ['en' => 'Tasty', 'de' => 'Lecker']);
		$this->assertSame('Tasty', $area->title());
	}

	public function testView(): void
	{
		$area = new Area(id: 'test', label: 'Test');
		$this->assertSame([
			'breadcrumb'      => [],
			'breadcrumbLabel' => 'Test',
			'icon'            => null,
			'id'              => 'test',
			'label'           => 'Test',
			'link'            => 'test',
			'search'          => null,
			'title'           => 'Test',
		], $area->view());
	}
}
