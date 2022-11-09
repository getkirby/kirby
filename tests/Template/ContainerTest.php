<?php

namespace Kirby\Template;

use Kirby\Exception\LogicException;

/**
 * @coversDefaultClass \Kirby\Template\Container
 */
class ContainerTest extends TestCase
{
	public function testContainer()
	{
		$container = new Container('test');

		$this->assertSame('test', $container->name);
		$this->assertSame('', $container->root);
		$this->assertFalse($container->open);
		$this->assertNull($container->parent);
		$this->assertSame([], $container->props);
	}

	/**
	 * @covers ::close
	 */
	public function testCloseWhenNotOpen()
	{
		$container = new Container('test');

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The container has not been opened');

		$container->close();
	}

	/**
	 * @covers ::file
	 */
	public function testFile()
	{
		$container = new Container('test');
		$this->assertSame('/test.php', $container->file());
	}

	/**
	 * @covers ::file
	 */
	public function testFileWithCustomRoot()
	{
		$container = new Container(name: 'test', root: __DIR__);
		$this->assertSame(__DIR__ . '/test.php', $container->file());
	}

	/**
	 * @covers ::open
	 * @covers ::close
	 */
	public function testNestedContainers()
	{
		$a = new Container(name: 'a');

		$a->open();

		$this->assertSame($a, Container::$current);

		$b = new Container(name: 'b');
		$b->open();

		$this->assertSame($b, Container::$current);

		$b->close();

		$this->assertSame($a, Container::$current);

		$a->close();

		$this->assertSame($a, $b->parent);
	}

	/**
	 * @covers ::open
	 * @covers ::close
	 */
	public function testOpenCloseWithSlotsAndSwallowedDefaultContent()
	{
		$container = new Container('test');
		$container->open();

		$container->slot();
		echo 'Default content';
		$container->endslot();

		echo 'Should be swallowed';

		$container->close();

		$slots = $container->slots();

		$this->assertSame('Default content', $slots->default()->render());
	}

	/**
	 * @covers ::open
	 * @covers ::close
	 */
	public function testOpenCloseWithDefaultSlotContent()
	{
		$container = new Container('test');
		$container->open();
		echo 'Default content';
		$container->close();

		$slots = $container->slots();

		$this->assertSame('Default content', $slots->default()->render());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderWithSlots()
	{
		$container = new Container(name: 'slots', root: __DIR__ . '/templates');

		// the template should be empty without any slots
		$this->assertSame('', trim($container->render()));

		$container->open();

		$container->slot('header');
		echo 'Header content';
		$container->endslot();

		$container->slot();
		echo 'Body content';
		$container->endslot();

		$container->slot('footer');
		echo 'Footer content';
		$container->endslot();

		$container->close();

		$expected  = 'Header content' . PHP_EOL;
		$expected .= 'Body content' . PHP_EOL;
		$expected .= 'Footer content';

		$this->assertSame($expected, $container->render());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderWithLazySlots()
	{
		$container = new Container(name: 'slots', root: __DIR__ . '/templates');

		$html = $container->render(slots: [
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
		$container = new Container(
			name: 'props',
			root: __DIR__ . '/templates',
			props: ['message' => 'hello']
		);

		$this->assertSame('hello', $container->render());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderWithLazyProps()
	{
		$container = new Container(
			name: 'props',
			root: __DIR__ . '/templates',
		);

		$this->assertSame('hello', $container->render(props: ['message' => 'hello']));
	}

	/**
	 * @covers ::scope
	 */
	public function testScope()
	{
		$container = new Container(name: 'test', props: $props = [
			'message' => 'Hello'
		]);

		$scope = $container->scope();

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
		$container = new Container('test');
		$container->slots = [
			'default' => $slot = new Slot($container, 'test')
		];

		$this->assertSame($slot, $container->scope()['slot']);
	}

	/**
	 * @covers ::scope
	 */
	public function testScopeWithProps()
	{
		$container = new Container(name: 'test');

		$scope = $container->scope(props: $props = [
			'message' => 'Hello'
		]);

		$this->assertSame('Hello', $scope['message']);
		$this->assertSame($props, $scope['props']);
	}

	public function testSelfClosing()
	{
		$container = new Container('test');
		$this->assertFalse($container->selfClosing);

		$container = new Container('test/');
		$this->assertTrue($container->selfClosing);
		$this->assertSame('test', $container->name);
	}

	/**
	 * @covers ::open
	 * @covers ::close
	 * @covers ::slot
	 * @covers ::endslot
	 */
	public function testSlots()
	{
		$container = new Container('test');
		$container->open();

		$container->slot('header');
		echo 'Header';
		$container->endslot();

		$container->slot('footer');
		echo 'Footer';
		$container->endslot();

		$container->close();

		$slots = $container->slots();

		$this->assertSame('Header', $slots->header()->render());
		$this->assertSame('Footer', $slots->footer()->render());
	}
}
