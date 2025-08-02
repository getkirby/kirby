<?php

namespace Kirby\Panel\Ui;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Button\ViewButtons;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(View::class)]
class ViewTest extends TestCase
{
	public function testButtons(): void
	{
		$view = new View(
			component: 'k-my-view',
			buttons: [
				['text' => 'My Button']
			]
		);

		$buttons = $view->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
		$buttons = $buttons->render();
		$this->assertCount(1, $buttons);
		$this->assertSame('k-view-button', $buttons[0]['component']);
		$this->assertSame('My Button', $buttons[0]['props']['text']);
	}

	public function testButtonsAsObject(): void
	{
		$buttons = new ViewButtons([
			['text' => 'My Button']
		]);

		$view = new View(
			component: 'k-my-view',
			buttons: $buttons
		);

		$buttons = $view->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
		$buttons = $buttons->render();
		$this->assertCount(1, $buttons);
		$this->assertSame('k-view-button', $buttons[0]['component']);
		$this->assertSame('My Button', $buttons[0]['props']['text']);
	}

	public function testProps(): void
	{
		$view = new View(
			component: 'k-my-view',
			title: 'My View'
		);

		$this->assertSame([
			'class'   => null,
			'style'   => null,
			'buttons' => [],
			'title'   => 'My View',
		], $view->props());
	}

	public function testRender(): void
	{
		$view = new View(
			component: 'k-my-view',
			breadcrumb: $breadcrumb = [
				['label' => 'My Breadcrumb', 'link' => '/my-breadcrumb']
			],
			search: 'users',
			title: 'My View'
		);

		$result = $view->render();
		$this->assertSame('k-my-view', $result['component']);
		$this->assertSame($breadcrumb, $result['breadcrumb']);
		$this->assertSame('users', $result['search']);
		$this->assertSame('My View', $result['title']);
	}
}
