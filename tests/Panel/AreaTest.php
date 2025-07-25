<?php

namespace Kirby\Panel;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Area::class)]
class AreaTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Area';

	public function testBreadcrumbLabel(): void
	{
		$area = new Area(id: 'test', label: 'Test');
		$this->assertSame('Test', $area->breadcrumbLabel());

		$area = new Area(id: 'test', breadcrumbLabel: 'Better', label: 'Test');
		$this->assertSame('Better', $area->breadcrumbLabel());
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
			'search'          => null,
			'title'           => 'Test',
		], $area->view());
	}
}
