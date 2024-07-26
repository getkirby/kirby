<?php

namespace Kirby\Panel\Ui;

use Closure;
use Kirby\Panel\Ui\Buttons\ViewButtons;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\View
 * @covers ::__construct
 */
class ViewTest extends TestCase
{
	/**
	 * @covers ::breadcrumb
	 */
	public function testBreadcrumb()
	{
		$view      = new View(id: 'test');
		$breadcrumb = $view->breadcrumb();
		$this->assertSame([], $breadcrumb);
	}

	/**
	 * @covers ::buttons
	 */
	public function testButtons()
	{
		$view    = new View(id: 'test');
		$buttons = $view->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
		$this->assertSame([], $buttons->render());
	}

	/**
	 * @covers ::props
	 */
	public function testProps()
	{
		$view  = new View(id: 'test');
		$props = $view->props();
		$this->assertInstanceOf(Closure::class, $props['buttons']);
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$view   = new View(id: 'test', title: 'Test', search: 'users');
		$result = $view->render();

		$this->assertSame('k-test-view', $result['component']);
		$this->assertSame('Test', $result['title']);
		$this->assertSame('users', $result['search']);
		$this->assertInstanceOf(Closure::class, $result['breadcrumb']);
		$this->assertSame([], $result['breadcrumb']());
		$this->assertInstanceOf(Closure::class, $result['props']['buttons']);
		$this->assertSame([], $result['props']['buttons']());

	}
}
