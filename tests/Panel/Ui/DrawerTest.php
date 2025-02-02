<?php

namespace Kirby\Panel\Ui;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Drawer
 * @covers ::__construct
 */
class DrawerTest extends TestCase
{
	/**
	 * @covers ::props
	 */
	public function testProps()
	{
		$drawer = new Drawer(
			class: 'k-my-drawer',
			title: 'My Drawer'
		);

		$this->assertSame([
			'class'    => 'k-my-drawer',
			'style'    => null,
			'disabled' => false,
			'icon'     => null,
			'options'  => null,
			'title'    => 'My Drawer'
		], $drawer->props());
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$drawer = new Drawer(
			component: 'k-test',
		);

		$result = $drawer->render();
		$this->assertSame('k-test', $result['component']);
	}
}
