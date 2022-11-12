<?php

namespace Kirby\Template;

use Kirby\Exception\LogicException;

class SnippetTest extends TestCase
{
	public function testSnippet()
	{
		$snippet = new Snippet('test');

		$this->assertSame('test', $snippet->name);
		$this->assertSame('', $snippet->root);
		$this->assertFalse($snippet->open);
		$this->assertNull($snippet->parent);
		$this->assertSame([], $snippet->props);
	}

	public function testCloseWhenNotOpen()
	{
		$snippet = new Snippet('test');

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The snippet has not been opened');

		$snippet->close();
	}

	public function testFile()
	{
		$snippet = new Snippet('test');
		$this->assertSame('/test.php', $snippet->file());
	}

	public function testFileWithCustomRoot()
	{
		$snippet = new Snippet(name: 'test', root: __DIR__);
		$this->assertSame(__DIR__ . '/test.php', $snippet->file());
	}

	public function testHelpers()
	{
		ob_start();

		Snippet::begin(name: 'simple', root: __DIR__ . '/templates');
		Slot::begin();
		echo 'Nice';
		Slot::end();
		Snippet::end();

		$this->assertSame('Nice', ob_get_clean());
	}

	public function testNestedSnippets()
	{
		$a = new Snippet(name: 'a');

		$a->open();

		$this->assertSame($a, Snippet::$current);

		$b = new Snippet(name: 'b');
		$b->open();

		$this->assertSame($b, Snippet::$current);

		$b->close();

		$this->assertSame($a, Snippet::$current);

		$a->close();

		$this->assertSame($a, $b->parent);
	}

	public function testOpenCloseWithSlotsAndSwallowedDefaultContent()
	{
		$snippet = new Snippet('test');
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
		$snippet = new Snippet('test');
		$snippet->open();
		echo 'Default content';
		$snippet->close();

		$slots = $snippet->slots();

		$this->assertSame('Default content', $slots->default()->render());
	}

	public function testRenderWithSlots()
	{
		$snippet = new Snippet(name: 'slots', root: __DIR__ . '/templates');

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
		$snippet = new Snippet(name: 'slots', root: __DIR__ . '/templates');

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

	public function testRenderWithProps()
	{
		$snippet = new Snippet(
			name: 'props',
			root: __DIR__ . '/templates',
			props: ['message' => 'hello']
		);

		$this->assertSame('hello', $snippet->render());
	}

	public function testRenderWithLazyProps()
	{
		$snippet = new Snippet(
			name: 'props',
			root: __DIR__ . '/templates',
		);

		$this->assertSame('hello', $snippet->render(props: ['message' => 'hello']));
	}

	public function testScope()
	{
		$snippet = new Snippet(name: 'test', props: $props = [
			'message' => 'Hello'
		]);

		$scope = $snippet->scope();

		$this->assertSame('Hello', $scope['message']);
		$this->assertSame($props, $scope['props']);
		$this->assertInstanceOf(Slots::class, $scope['slots']);
		$this->assertNull($scope['slot']);
	}

	public function testScopeWithDefaultSlot()
	{
		$snippet = new Snippet('test');
		$snippet->slots = [
			'default' => $slot = new Slot($snippet, 'test')
		];

		$this->assertSame($slot, $snippet->scope()['slot']);
	}

	public function testScopeWithProps()
	{
		$snippet = new Snippet(name: 'test');

		$scope = $snippet->scope(props: $props = [
			'message' => 'Hello'
		]);

		$this->assertSame('Hello', $scope['message']);
		$this->assertSame($props, $scope['props']);
	}

	public function testSlots()
	{
		$snippet = new Snippet('test');
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
