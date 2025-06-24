<?php

namespace Kirby\Template;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use ReflectionProperty;

/**
 * @coversDefaultClass \Kirby\Template\Snippet
 */
class SnippetTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

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
		// all output must be captured
		$this->expectOutputString('');

		new App([
			'roots' => [
				'snippets' => static::FIXTURES
			]
		]);

		$openProp = new ReflectionProperty(Snippet::class, 'open');
		$openProp->setAccessible(true);

		$snippet = Snippet::factory('data', ['message' => 'hello']);
		$this->assertSame('hello', $snippet);

		$snippet = Snippet::factory('simple', slots: true);
		$this->assertInstanceOf(Snippet::class, $snippet);
		$this->assertTrue($openProp->getValue($snippet));
		$snippet->close(); // close output buffers to reset global state

		$snippet = Snippet::factory(null, ['message' => 'hello']);
		$this->assertSame('', $snippet);

		$snippet = Snippet::factory('missin', ['message' => 'hello']);
		$this->assertSame('', $snippet);

		$snippet = Snippet::factory('missin', ['message' => 'hello'], slots: true);
		$this->assertInstanceOf(Snippet::class, $snippet);
		$snippet->close(); // close output buffers to reset global state

		$snippet = Snippet::factory(null, ['message' => 'hello'], slots: true);
		$this->assertInstanceOf(Snippet::class, $snippet);
		$snippet->close(); // close output buffers to reset global state
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
				'snippets' => static::FIXTURES
			]
		]);

		$this->assertSame(static::FIXTURES . '/simple.php', Snippet::file('simple'));
		$this->assertSame(static::FIXTURES . '/simple.php', Snippet::file(['missin', 'simple']));
		$this->assertNull(Snippet::file('missin'));
		$this->assertSame('bar.php', Snippet::file('foo'));
	}

	/**
	 * @covers ::begin
	 * @covers ::end
	 */
	public function testHelpers()
	{
		ob_start();

		Snippet::begin(static::FIXTURES . '/simple.php');
		Slot::begin();
		echo 'Nice';
		Slot::end();
		Snippet::end();

		$this->assertSame('Nice', ob_get_clean());
	}

	/**
	 * @covers ::open
	 * @covers ::close
	 * @covers ::parent
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

		$this->assertSame($a, $b->parent());
	}

	/**
	 * @covers ::open
	 * @covers ::close
	 */
	public function testOpenCloseWithSlotsAndSwallowedDefaultContent()
	{
		// all output must be captured
		$this->expectOutputString('');

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
		// all output must be captured
		$this->expectOutputString('');

		$snippet = new Snippet('test.php');
		$snippet->open();
		echo 'Default content';
		$snippet->close();

		$slots = $snippet->slots();

		$this->assertSame('Default content', $slots->default()->render());
	}

	public static function renderWithSlotsProvider(): array
	{
		return [
			[static::FIXTURES . '/slots.php', 'Header content' . PHP_EOL . 'Body content' . PHP_EOL . 'Footer content'],
			[static::FIXTURES . '/missin.php', ''],
			[null, ''],
		];
	}

	/**
	 * @covers ::render
	 * @dataProvider renderWithSlotsProvider
	 */
	public function testRenderWithSlots(string|null $file, string $expected)
	{
		// all output must be captured
		$this->expectOutputString('');

		$snippet = new Snippet($file);

		// the template should be empty without any slots
		$this->assertSame('', trim($snippet->render()));

		$snippet->open();

		$snippet->slot('header');
		echo 'Header content';
		$snippet->endslot();

		$snippet->slot('footer');
		echo 'Footer content';
		$snippet->endslot();

		$snippet->slot();
		echo 'Body content';
		$snippet->endslot();

		$snippet->close();

		$this->assertSame($expected, $snippet->render());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderWithoutClosing()
	{
		// all output must be captured
		$this->expectOutputString('');

		$snippet = new Snippet(static::FIXTURES . '/layout.php');
		$snippet->open();
		echo 'content';

		$this->assertSame("<h1>Layout</h1>\ncontent<footer>with other stuff</footer>\n", $snippet->render());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderWithoutClosingAndMultipleSlots()
	{
		// all output must be captured
		$this->expectOutputString('');

		$snippet = new Snippet(static::FIXTURES . '/layout-with-multiple-slots.php');

		$snippet->slot('header');
		echo 'Header content';
		$snippet->endslot();

		$snippet->slot();
		echo 'Body content';
		$snippet->endslot();

		$this->assertSame("<h1>Layout</h1>\n<header>Header content</header>\n<main>Body content</main>\n", $snippet->render());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderWithLazySlots()
	{
		$snippet = new Snippet(static::FIXTURES . '/slots.php');

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
	 * @covers ::__construct
	 * @covers ::render
	 */
	public function testRenderWithData()
	{
		$snippet = new Snippet(
			file: static::FIXTURES . '/data.php',
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
			file: static::FIXTURES . '/data.php',
		);

		$this->assertSame('hello', $snippet->render(data: ['message' => 'hello']));
	}

	/**
	 * @covers ::root
	 */
	public function testRoot()
	{
		new App([
			'roots' => [
				'snippets' => $root = static::FIXTURES
			]
		]);

		$this->assertSame($root, Snippet::root());
	}

	/**
	 * @covers ::scope
	 */
	public function testScope()
	{
		$closure = function ($scope) use (&$data) {
			$this->assertArrayHasKey('slots', $scope);
			$this->assertArrayHasKey('slot', $scope);
			$this->assertArrayHasKey('closure', $scope);
			$this->assertArrayHasKey('message', $scope);

			$this->assertSame('Hello', $scope['message']);
			$this->assertInstanceOf(Slots::class, $scope['slots']);
			$this->assertNull($scope['slots']->default);
			$this->assertNull($scope['slot']);

			// print success output to ensure that this code ran at all
			echo 'Scope snippet success';
		};

		$snippet = new Snippet(file: static::FIXTURES . '/scope.php', data: $data = [
			'message' => 'Hello',
			'closure' => $closure
		]);

		$this->assertSame('Scope snippet success', $snippet->render());
	}

	/**
	 * @covers ::scope
	 */
	public function testScopeWithDefaultSlot()
	{
		$closure = function ($scope) use (&$data, &$slot) {
			$this->assertArrayHasKey('closure', $scope);
			$this->assertArrayHasKey('slots', $scope);
			$this->assertArrayHasKey('slot', $scope);

			$this->assertInstanceOf(Slots::class, $scope['slots']);
			$this->assertSame($slot, $scope['slots']->default);
			$this->assertSame($slot, $scope['slot']);

			// print success output to ensure that this code ran at all
			echo 'Scope snippet success';
		};

		$snippet = new Snippet(file: static::FIXTURES . '/scope.php', data: $data = [
			'closure' => $closure
		]);

		$slotsProp = new ReflectionProperty($snippet, 'slots');
		$slotsProp->setAccessible(true);

		$slotsProp->setValue($snippet, [
			'default' => $slot = new Slot('test')
		]);

		$this->assertSame('Scope snippet success', $snippet->render());
	}

	/**
	 * @covers ::scope
	 */
	public function testScopeWithoutSlots()
	{
		new App([
			'roots' => [
				'snippets' => static::FIXTURES
			]
		]);

		$slots = null;

		$closure = function ($scope) use (&$data, &$slots) {
			$this->assertArrayHasKey('slots', $scope);
			$this->assertArrayHasKey('slot', $scope);
			$this->assertArrayHasKey('closure', $scope);
			$this->assertArrayHasKey('message', $scope);

			$this->assertSame('Hello', $scope['message']);
			$this->assertInstanceOf(Slots::class, $scope['slots']);
			$this->assertNull($scope['slots']->default);
			$this->assertNull($scope['slot']);

			if ($slots !== null) {
				$this->assertSame($slots, $scope['slots']);
			} else {
				$slots = $scope['slots'];
			}

			// print success output to ensure that this code ran at all
			echo 'Scope snippet success';
		};

		$result = Snippet::factory(
			name: 'scope',
			data: $data = [
				'message' => 'Hello',
				'closure' => $closure
			]
		);
		$this->assertSame('Scope snippet success', $result);

		// second run to test the dummy slots cache
		$result = Snippet::factory(
			name: 'scope',
			data: $data = [
				'message' => 'Hello',
				'closure' => $closure
			]
		);
		$this->assertSame('Scope snippet success', $result);
	}

	/**
	 * @covers ::scope
	 */
	public function testScopeWithInvalidData()
	{
		new App([
			'roots' => [
				'snippets' => static::FIXTURES
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Passing the $slot or $slots variables to snippets is not supported.');

		Snippet::factory(
			name: 'scope',
			data: [
				'slot' => 'Hello'
			]
		);
	}

	/**
	 * @covers ::slots
	 * @covers ::slot
	 * @covers ::endslot
	 */
	public function testSlots()
	{
		// all output must be captured
		$this->expectOutputString('');

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
