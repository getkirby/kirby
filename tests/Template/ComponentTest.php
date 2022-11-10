<?php

namespace Kirby\Template;

use Kirby\Exception\LogicException;

/**
 * @coversDefaultClass \Kirby\Template\Component
 */
class ComponentTest extends TestCase
{
	public function testComponent()
	{
		$component = new Component('test');

		$this->assertSame('test', $component->name);
		$this->assertSame('', $component->root);
		$this->assertFalse($component->open);
		$this->assertNull($component->parent);
		$this->assertSame([], $component->props);
	}

	/**
	 * @covers ::close
	 */
	public function testCloseWhenNotOpen()
	{
		$component = new Component('test');

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The component has not been opened');

		$component->close();
	}

	/**
	 * @covers ::file
	 */
	public function testFile()
	{
		$component = new Component('test');
		$this->assertSame('/test.php', $component->file());
	}

	/**
	 * @covers ::file
	 */
	public function testFileWithCustomRoot()
	{
		$component = new Component(name: 'test', root: __DIR__);
		$this->assertSame(__DIR__ . '/test.php', $component->file());
	}

	/**
	 * @covers ::begin
	 * @covers ::end
	 */
	public function testHelpers()
	{
		ob_start();

		Component::begin(name: 'simple', root: __DIR__ . '/templates');
		Slot::begin();
		echo 'Nice';
		Slot::end();
		Component::end();

		$this->assertSame('Nice', ob_get_clean());
	}

	/**
	 * @covers ::open
	 * @covers ::close
	 */
	public function testNestedComponents()
	{
		$a = new Component(name: 'a');

		$a->open();

		$this->assertSame($a, Component::$current);

		$b = new Component(name: 'b');
		$b->open();

		$this->assertSame($b, Component::$current);

		$b->close();

		$this->assertSame($a, Component::$current);

		$a->close();

		$this->assertSame($a, $b->parent);
	}

	/**
	 * @covers ::open
	 * @covers ::close
	 */
	public function testOpenCloseWithSlotsAndSwallowedDefaultContent()
	{
		$component = new Component('test');
		$component->open();

		$component->slot();
		echo 'Default content';
		$component->endslot();

		echo 'Should be swallowed';

		$component->close();

		$slots = $component->slots();

		$this->assertSame('Default content', $slots->default()->render());
	}

	/**
	 * @covers ::open
	 * @covers ::close
	 */
	public function testOpenCloseWithDefaultSlotContent()
	{
		$component = new Component('test');
		$component->open();
		echo 'Default content';
		$component->close();

		$slots = $component->slots();

		$this->assertSame('Default content', $slots->default()->render());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderWithSlots()
	{
		$component = new Component(name: 'slots', root: __DIR__ . '/templates');

		// the template should be empty without any slots
		$this->assertSame('', trim($component->render()));

		$component->open();

		$component->slot('header');
		echo 'Header content';
		$component->endslot();

		$component->slot();
		echo 'Body content';
		$component->endslot();

		$component->slot('footer');
		echo 'Footer content';
		$component->endslot();

		$component->close();

		$expected  = 'Header content' . PHP_EOL;
		$expected .= 'Body content' . PHP_EOL;
		$expected .= 'Footer content';

		$this->assertSame($expected, $component->render());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderWithLazySlots()
	{
		$component = new Component(name: 'slots', root: __DIR__ . '/templates');

		$html = $component->render(slots: [
			'header'  => 'Header content',
			'default' => 'Body content',
			'footer'  => 'Footer content'
		]);

		$expected  = 'Header content' . PHP_EOL;
		$expected .= 'Body content' . PHP_EOL;
		$expected .= 'Footer content';

		$this->assertSame($expected, $html);
	}

	/**
	 * @covers ::render
	 */
	public function testRenderWithProps()
	{
		$component = new Component(
			name: 'props',
			root: __DIR__ . '/templates',
			props: ['message' => 'hello']
		);

		$this->assertSame('hello', $component->render());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderWithLazyProps()
	{
		$component = new Component(
			name: 'props',
			root: __DIR__ . '/templates',
		);

		$this->assertSame('hello', $component->render(props: ['message' => 'hello']));
	}

	/**
	 * @covers ::scope
	 */
	public function testScope()
	{
		$component = new Component(name: 'test', props: $props = [
			'message' => 'Hello'
		]);

		$scope = $component->scope();

		$this->assertSame('Hello', $scope['message']);
		$this->assertSame($props, $scope['props']);
		$this->assertInstanceOf(Slots::class, $scope['slots']);
		$this->assertNull($scope['slot']);
	}

	/**
	 * @covers ::scope
	 */
	public function testScopeWithDefaultSlot()
	{
		$component = new Component('test');
		$component->slots = [
			'default' => $slot = new Slot($component, 'test')
		];

		$this->assertSame($slot, $component->scope()['slot']);
	}

	/**
	 * @covers ::scope
	 */
	public function testScopeWithProps()
	{
		$component = new Component(name: 'test');

		$scope = $component->scope(props: $props = [
			'message' => 'Hello'
		]);

		$this->assertSame('Hello', $scope['message']);
		$this->assertSame($props, $scope['props']);
	}

	public function testSelfClosing()
	{
		$component = new Component('test');
		$this->assertFalse($component->selfClosing);

		$component = new Component('test/');
		$this->assertTrue($component->selfClosing);
		$this->assertSame('test', $component->name);
	}

	/**
	 * @covers ::open
	 * @covers ::close
	 * @covers ::slot
	 * @covers ::endslot
	 */
	public function testSlots()
	{
		$component = new Component('test');
		$component->open();

		$component->slot('header');
		echo 'Header';
		$component->endslot();

		$component->slot('footer');
		echo 'Footer';
		$component->endslot();

		$component->close();

		$slots = $component->slots();

		$this->assertSame('Header', $slots->header()->render());
		$this->assertSame('Footer', $slots->footer()->render());
	}
}
