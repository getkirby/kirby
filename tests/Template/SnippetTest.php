<?php

namespace Kirby\Template;

use Kirby\Cms\App;
use Kirby\Exception\LogicException;

/**
 * @coversDefaultClass Kirby\Template\Snippet
 */
class SnippetTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testSnippet()
	{
		$snippet = new Snippet('test.php');

		$this->assertSame('test.php', $snippet->file);
		$this->assertFalse($snippet->open);
		$this->assertNull($snippet->parent);
		$this->assertSame([], $snippet->data);
	}

	/**
	 * @covers ::close
	 */
	public function testCloseWhenNotOpen()
	{
		$snippet = new Snippet('test.php');

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The snippet has not been opened');

		$snippet->close();
	}

	/**
	 * @covers ::factory
	 */
	public function testFactory()
	{
		new App([
			'roots' => [
				'snippets' => __DIR__ . '/templates'
			]
		]);

		$snippet = Snippet::factory('simple', ['slot' => 'hello']);
		$this->assertSame('hello', $snippet);

		$snippet = Snippet::factory('simple', ['slot' => 'hello'], slots: true);
		$this->assertInstanceOf(Snippet::class, $snippet);
		$this->assertTrue($snippet->open);

		$snippet->close();
	}


	/**
	 * @covers ::file
	 */
	public function testFile()
	{
		App::plugin('test/d', [
			'snippets' => [
				'foo' => 'bar.php'
			]
		]);

		new App([
			'roots' => [
				'snippets' => __DIR__ . '/templates'
			]
		]);

		$this->assertSame(__DIR__ . '/templates/simple.php', Snippet::file('simple'));
		$this->assertSame(__DIR__ . '/templates/simple.php', Snippet::file(['missin', 'simple']));
		$this->assertSame('bar.php', Snippet::file('foo'));
	}

	/**
	 * @covers ::begin
	 * @covers ::end
	 */
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

	/**
	 * @covers ::open
	 * @covers ::close
	 */
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

	/**
	 * @covers ::open
	 * @covers ::close
	 */
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

	/**
	 * @covers ::open
	 * @covers ::close
	 */
	public function testOpenCloseWithDefaultSlotContent()
	{
		$snippet = new Snippet('test.php');
		$snippet->open();
		echo 'Default content';
		$snippet->close();

		$slots = $snippet->slots();

		$this->assertSame('Default content', $slots->default()->render());
	}

	/**
	 * @covers ::render
	 */
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

	/**
	 * @covers ::render
	 */
	public function testRenderWithoutClosing()
	{
		$snippet = new Snippet(__DIR__ . '/templates/layout.php');
		$snippet->open();
		echo 'content';

		$this->assertSame('<h1>Layout</h1>
content', $snippet->render());
	}

	/**
	 * @covers ::render
	 */
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

	/**
	 * @covers ::render
	 */
	public function testRenderWithData()
	{
		$snippet = new Snippet(
			file: __DIR__ . '/templates/data.php',
			data: ['message' => 'hello']
		);

		$this->assertSame('hello', $snippet->render());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderWithLazyData()
	{
		$snippet = new Snippet(
			file: __DIR__ . '/templates/data.php',
		);

		$this->assertSame('hello', $snippet->render(data: ['message' => 'hello']));
	}

	/**
	 * @covers ::scope
	 */
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

	/**
	 * @covers ::scope
	 */
	public function testScopeWithDefaultSlot()
	{
		$snippet = new Snippet('test.php');
		$snippet->slots = [
			'default' => $slot = new Slot($snippet, 'test')
		];

		$this->assertSame($slot, $snippet->scope()['slot']);
	}

	/**
	 * @covers ::scope
	 */
	public function testScopeWithData()
	{
		$snippet = new Snippet(file: 'test.php');

		$scope = $snippet->scope(data: $data = [
			'message' => 'Hello'
		]);

		$this->assertSame('Hello', $scope['message']);
		$this->assertSame($data, $scope['data']);
	}

	/**
	 * @covers ::slots
	 * @covers ::slot
	 * @covers ::endslot
	 */
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
