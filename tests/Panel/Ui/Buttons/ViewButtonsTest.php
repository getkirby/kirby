<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\Page;
use Kirby\Panel\Areas\AreaTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ViewButtons::class)]
class ViewButtonsTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->app([
			'options' => [
				'panel' => [
					'viewButtons' => [
						'test' => [
							'a' => ['component' => 'result-a'],
							'b' => ['component' => 'result-b'],
							'c' => ['component' => 'result-c'],
							'z' => function () {
								return null;
							}
						]
					]
				]
			]
		]);
	}

	public function testConstruct(): void
	{
		// no buttons
		$buttons = new ViewButtons([]);
		$this->assertCount(0, $buttons->buttons);

		// passed directly as array
		$buttons = new ViewButtons(['a', 'b']);
		$this->assertCount(2, $buttons->buttons);

		$buttons = new ViewButtons(['a', 'b'], view: 'test');
		$this->assertCount(2, $buttons->buttons);

		// passed directly as ViewButton
		$buttons = new ViewButtons([
			new ViewButton(text: 'Button A'),
			new ViewButton(text: 'Button B'),
		]);
		$this->assertCount(2, $buttons->buttons);

		// from options
		$buttons = new ViewButtons(view: 'test');
		$this->assertCount(4, $buttons->buttons);
	}

	public function testBind(): void
	{
		$buttons = new ViewButtons();
		$this->assertSame([], $buttons->data);

		$buttons = new ViewButtons(data: ['foo' => 'bar']);
		$this->assertSame(['foo' => 'bar'], $buttons->data);

		$buttons = new ViewButtons(data: ['foo' => 'bar']);
		$buttons->bind(['homer' => 'simpson']);
		$this->assertSame(['foo' => 'bar', 'homer' => 'simpson'], $buttons->data);
	}

	public function testDefaults(): void
	{
		$buttons = new ViewButtons();
		$this->assertCount(0, $buttons->buttons ?? []);

		$buttons->defaults('a', 'b');
		$this->assertCount(2, $buttons->buttons);
	}

	public function testRender(): void
	{
		$buttons = new ViewButtons(buttons: ['a', 'y'], view: 'test');
		$result  = $buttons->render();

		$this->assertCount(2, $result);
		$this->assertSame('result-a', $result[0]['component']);
		$this->assertSame('k-y-view-button', $result[1]['component']);
	}

	public function testRenderFromConfig(): void
	{
		$buttons = new ViewButtons(view: 'test');
		$result  = $buttons->render();

		$this->assertCount(3, $result);
		$this->assertSame('result-a', $result[0]['component']);
		$this->assertSame('result-b', $result[1]['component']);
		$this->assertSame('result-c', $result[2]['component']);
	}

	public function testRenderWithViewButtonObjects(): void
	{
		$buttons = new ViewButtons([
			new ViewButton(text: 'Button A'),
			new ViewButton(text: 'Button B'),
		]);

		$result = $buttons->render();
		$this->assertCount(2, $result);
		$this->assertSame('Button A', $result[0]['props']['text']);
		$this->assertSame('Button B', $result[1]['props']['text']);
	}

	public function testRenderNoButtons(): void
	{
		$buttons = new ViewButtons(buttons: false);
		$this->assertSame([], $buttons->render());
	}

	public function testView(): void
	{
		// view name
		$buttons = ViewButtons::view('page');
		$this->assertCount(0, $buttons->buttons ?? []);
		$this->assertNull($buttons->model);

		// view model
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'buttons' => ['a', 'b']
			]
		]);

		$buttons = ViewButtons::view($page->panel());
		$this->assertCount(2, $buttons->buttons);
		$this->assertSame($page, $buttons->model);
	}
}
