<?php

namespace Kirby\Template;

use Kirby\Exception\LogicException;

class SnippetTest extends TestCase
{
	public function testSnippet()
	{
		$snippet = new Snippet('test.php');

		$this->assertSame('test.php', $snippet->file);
		$this->assertFalse($snippet->open);
		$this->assertNull($snippet->parent);
		$this->assertSame([], $snippet->data);
	}

	public function testCloseWhenNotOpen()
	{
		$snippet = new Snippet('test.php');

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The component has not been opened');

		$snippet->close();
	}

	public function testHelpers()
	{
		ob_start();

		Snippet::begin(__DIR__ . '/templates/simple.php');
		Slot::begin();
		echo 'Nice';
		Slot::end();
		Snippet::end();

		$this->assertSame('Nice', ob_get_clean());
	}

	public function testNestedComponents()
	{
		$a = new Snippet(file: 'a.php');

		$a->open();

		$this->assertSame($a, Snippet::$current);

		$b = new Snippet(file: 'b.php');
		$b->open();

		$this->assertSame($b, Snippet::$current);

		$b->close();

		$this->assertSame($a, Snippet::$current);

		$a->close();

		$this->assertSame($a, $b->parent);
	}

	public function testOpenCloseWithSlotsAndSwallowedDefaultContent()
	{
		$snippet = new Snippet('test.php');
		$snippet->open();

		$snippet->slot();
		echo 'Default content';
		$snippet->endslot();

		echo 'Should be swallowed';

		$snippet->close();

		$slots = $snippet->slots();

		$this->assertSame('Default content', $slots->default()->render());
	}

	public function testOpenCloseWithDefaultSlotContent()
	{
		$snippet = new Snippet('test.php');
		$snippet->open();
		echo 'Default content';
		$snippet->close();

		$slots = $snippet->slots();

		$this->assertSame('Default content', $slots->default()->render());
	}

	public function testRenderWithSlots()
	{
		$snippet = new Snippet(__DIR__ . '/templates/slots.php');

		// the template should be empty without any slots
		$this->assertSame('', trim($snippet->render()));

		$snippet->open();

		$snippet->slot('header');
		echo 'Header content';
		$snippet->endslot();

		$snippet->slot();
		echo 'Body content';
		$snippet->endslot();

		$snippet->slot('footer');
		echo 'Footer content';
		$snippet->endslot();

		$snippet->close();

		$expected  = 'Header content' . PHP_EOL;
		$expected .= 'Body content' . PHP_EOL;
		$expected .= 'Footer content';

		$this->assertSame($expected, $snippet->render());
	}

	public function testRenderWithLazySlots()
	{
		$snippet = new Snippet(__DIR__ . '/templates/slots.php');

		$html = $snippet->render(slots: [
			'header'  => 'Header content',
			'default' => 'Body content',
			'footer'  => 'Footer content'
		]);

		$expected  = 'Header content' . PHP_EOL;
		$expected .= 'Body content' . PHP_EOL;
		$expected .= 'Footer content';

		$this->assertSame($expected, $html);
	}

	public function testRenderWithData()
	{
		$snippet = new Snippet(
			file: __DIR__ . '/templates/data.php',
			data: ['message' => 'hello']
		);

		$this->assertSame('hello', $snippet->render());
	}

	public function testRenderWithLazyData()
	{
		$snippet = new Snippet(
			file: __DIR__ . '/templates/data.php',
		);

		$this->assertSame('hello', $snippet->render(data: ['message' => 'hello']));
	}

	public function testScope()
	{
		$snippet = new Snippet(file: 'test.php', data: $data = [
			'message' => 'Hello'
		]);

		$scope = $snippet->scope();

		$this->assertSame('Hello', $scope['message']);
		$this->assertSame($data, $scope['data']);
		$this->assertInstanceOf(Slots::class, $scope['slots']);
		$this->assertNull($scope['slot']);
	}

	public function testScopeWithDefaultSlot()
	{
		$snippet = new Snippet('test.php');
		$snippet->slots = [
			'default' => $slot = new Slot($snippet, 'test')
		];

		$this->assertSame($slot, $snippet->scope()['slot']);
	}

	public function testScopeWithData()
	{
		$snippet = new Snippet(file: 'test.php');

		$scope = $snippet->scope(data: $data = [
			'message' => 'Hello'
		]);

		$this->assertSame('Hello', $scope['message']);
		$this->assertSame($data, $scope['data']);
	}

	public function testSlots()
	{
		$snippet = new Snippet('test.php');
		$snippet->open();

		$snippet->slot('header');
		echo 'Header';
		$snippet->endslot();

		$snippet->slot('footer');
		echo 'Footer';
		$snippet->endslot();

		$snippet->close();

		$slots = $snippet->slots();

		$this->assertInstanceOf(Slots::class, $slots);
		$this->assertSame('Header', $slots->header()->render());
		$this->assertSame('Footer', $slots->footer()->render());
	}
}
