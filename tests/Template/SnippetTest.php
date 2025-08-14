<?php

namespace Kirby\Template;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;

#[CoversClass(Snippet::class)]
class SnippetTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';

	public function testCloseWhenNotOpen(): void
	{
		$snippet = new Snippet('test.php');

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The snippet has not been opened');

		$snippet->close();
	}

	public function testFactory(): void
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

	public function testFile(): void
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

	public function testHelpers(): void
	{
		ob_start();

		Snippet::begin(static::FIXTURES . '/simple.php');
		Slot::begin();
		echo 'Nice';
		Slot::end();
		Snippet::end();

		$this->assertSame('Nice', ob_get_clean());
	}

	public function testNestedComponents(): void
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

	public function testOpenCloseWithSlotsAndSwallowedDefaultContent(): void
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

	public function testOpenCloseWithDefaultSlotContent(): void
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

	#[DataProvider('renderWithSlotsProvider')]
	public function testRenderWithSlots(
		string|null $file,
		string $expected
	): void {
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

	public function testRenderWithoutClosing(): void
	{
		// all output must be captured
		$this->expectOutputString('');

		$snippet = new Snippet(static::FIXTURES . '/layout.php');
		$snippet->open();
		echo 'content';

		$this->assertSame("<h1>Layout</h1>\ncontent<footer>with other stuff</footer>\n", $snippet->render());
	}

	public function testRenderWithoutClosingAndMultipleSlots(): void
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

	public function testRenderWithLazySlots(): void
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

	public function testRenderWithData(): void
	{
		$snippet = new Snippet(
			file: static::FIXTURES . '/data.php',
			data: ['message' => 'hello']
		);

		$this->assertSame('hello', $snippet->render());
	}

	public function testRenderWithLazyData(): void
	{
		$snippet = new Snippet(
			file: static::FIXTURES . '/data.php',
		);

		$this->assertSame('hello', $snippet->render(data: ['message' => 'hello']));
	}

	public function testRoot(): void
	{
		new App([
			'roots' => [
				'snippets' => $root = static::FIXTURES
			]
		]);

		$this->assertSame($root, Snippet::root());
	}

	public function testScope(): void
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

	public function testScopeWithDefaultSlot(): void
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

	public function testScopeWithoutSlots(): void
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

	public function testScopeWithInvalidData(): void
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

	public function testSlots(): void
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
