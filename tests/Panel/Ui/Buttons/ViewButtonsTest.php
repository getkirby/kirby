<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\Page;
use Kirby\Panel\Areas\AreaTestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Buttons\ViewButtons
 */
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
	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		// no buttons
		$buttons = new ViewButtons('test', []);
		$this->assertCount(0, $buttons->buttons);

		// passed directly
		$buttons = new ViewButtons('test', ['a', 'b']);
		$this->assertCount(2, $buttons->buttons);

		// from options
		$buttons = new ViewButtons('test');
		$this->assertCount(4, $buttons->buttons);
	}

	/**
	 * @covers ::defaults
	 */
	public function testDefaults()
	{
		$buttons = new ViewButtons('foo');
		$this->assertCount(0, $buttons->buttons ?? []);

		$buttons->defaults('a', 'b');
		$this->assertCount(2, $buttons->buttons);
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$buttons = new ViewButtons('test', ['a', 'b']);
		$result  = $buttons->render();

		$this->assertCount(2, $result);
		$this->assertSame('k-view-a-button', $result[0]['component']);
		$this->assertSame('k-view-b-button', $result[1]['component']);
	}

	/**
	 * @covers ::render
	 */
	public function testRenderFromConfig()
	{
		$buttons = new ViewButtons('test');
		$result  = $buttons->render();

		$this->assertCount(3, $result);
		$this->assertSame('result-a', $result[0]['component']);
		$this->assertSame('result-b', $result[1]['component']);
		$this->assertSame('result-c', $result[2]['component']);
	}

	/**
	 * @covers ::view
	 */
	public function testView()
	{
		// view name
		$buttons = ViewButtons::view('page');
		$this->assertCount(0, $buttons->buttons ?? []);

		// view model
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'buttons' => ['a', 'b']
			]
		]);

		$buttons = ViewButtons::view($page->panel());
		$this->assertCount(2, $buttons->buttons);
	}
}
