<?php

namespace Kirby\Panel\Ui;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Drawer::class)]
class DrawerTest extends TestCase
{
	public function testProps(): void
	{
		$drawer = new Drawer(
			class: 'k-my-drawer',
			title: 'My Drawer'
		);

		$this->assertSame([
			'class'    => 'k-my-drawer',
			'style'    => null,
			'icon'     => null,
			'options'  => null,
			'title'    => 'My Drawer'
		], $drawer->props());
	}

	public function testRender(): void
	{
		$drawer = new Drawer(
			component: 'k-test',
		);

		$result = $drawer->render();
		$this->assertSame('k-test', $result['component']);
	}
}
