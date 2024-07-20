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
						'test' => ['a', 'b', 'c']
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
		$result  = $buttons->render();
		$this->assertCount(0, $result);

		// passed directly
		$buttons = new ViewButtons('test', ['a', 'b']);
		$result  = $buttons->render();
		$this->assertCount(2, $result);

		// from options
		$buttons = new ViewButtons('test');
		$result  = $buttons->render();
		$this->assertCount(3, $result);
	}

	/**
	 * @covers ::defaults
	 */
	public function testDefaults()
	{
		$buttons = new ViewButtons('foo');
		$result  = $buttons->render();
		$this->assertCount(0, $result);

		$buttons->defaults('a', 'b');
		$result  = $buttons->render();
		$this->assertCount(2, $result);
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$buttons = new ViewButtons('test', ['a', 'b']);
		$result  = $buttons->render();

		$this->assertSame('k-view-a-button', $result[0]['component']);
		$this->assertSame('k-view-b-button', $result[1]['component']);
	}

	/**
	 * @covers ::view
	 */
	public function testView()
	{
		// view name
		$buttons = ViewButtons::view('page');
		$result  = $buttons->render();
		$this->assertCount(0, $result);

		// view model
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'buttons' => ['a', 'b']
			]
		]);

		$buttons = ViewButtons::view($page->panel());
		$result  = $buttons->render();
		$this->assertCount(2, $result);
	}
}
