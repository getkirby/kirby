<?php

namespace Kirby\Template;

use Kirby\Exception\LogicException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Stack::class)]
class StackTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';

	protected function setUp(): void
	{
		Stack::reset();
	}

	public function testCapture(): void
	{
		Stack::begin('footer');
		echo 'Hello';
		Stack::end();

		Stack::begin('footer');
		echo 'Hello';
		Stack::end();

		$output = Stack::render('footer');
		$this->assertSame('Hello' . PHP_EOL . 'Hello', $output);
	}

	public function testCaptureUnique(): void
	{
		Stack::begin('footer');
		echo 'Hello';
		Stack::end();

		Stack::begin('footer', unique: true);
		echo 'Hello';
		Stack::end();

		$output = Stack::render('footer');
		$this->assertSame('Hello', $output);
	}

	public function testDeferredRendering(): void
	{
		$output = Snippet::load(static::FIXTURES . '/stack-order.php');
		$this->assertSame('Hello', trim($output));
	}

	public function testEndWithClosedBuffer(): void
	{
		ob_start();
		echo 'outside';
		Stack::begin('head');
		echo 'inside';

		// closes the buffer of the stack
		ob_end_clean();

		try {
			$this->expectException(LogicException::class);
			$this->expectExceptionMessage('The "head" stack cannot be closed: its output was already ended inside of it, e.g. with `ob_end_clean()`');

			Stack::end();
		} finally {
			// the stack must not capture the surrounding output
			$this->assertSame('outside', ob_get_clean());
			$this->assertSame('', Stack::render('head'));
		}
	}

	public function testEndWithOpenBuffer(): void
	{
		Stack::begin('head');
		echo 'inside';

		// starts a buffer that is never closed
		ob_start();
		echo 'nested';

		try {
			$this->expectException(LogicException::class);
			$this->expectExceptionMessage('The "head" stack cannot be closed: an output buffer started inside of it with `ob_start()` is still open');

			Stack::end();
		} finally {
			// clean up both buffers
			ob_end_clean();
			ob_end_clean();
		}
	}

	public function testEndWithoutBegin(): void
	{
		Stack::end();
		$this->assertSame('', Stack::render('missing'));
	}

	public function testIsRendering(): void
	{
		$this->assertFalse(Stack::isRendering());

		Stack::open();
		$this->assertTrue(Stack::isRendering());

		Stack::open();
		$this->assertTrue(Stack::isRendering());

		Stack::close();
		$this->assertTrue(Stack::isRendering());

		Stack::close();
		$this->assertFalse(Stack::isRendering());
	}

	public function testHelpers(): void
	{
		push('head');
		echo 'Meta';
		endpush();

		$output = stack('head', return: true);
		$this->assertSame('Meta', $output);
	}

	public function testHelpersEcho(): void
	{
		push('body', 'Content');

		ob_start();
		stack('body');
		$this->assertSame('Content', ob_get_clean());
	}

	public function testPushAndRenderClears(): void
	{
		Stack::push('scripts', 'a');
		Stack::push('scripts', 'b');

		$this->assertSame('a' . PHP_EOL . 'b', Stack::render('scripts'));
		$this->assertSame('', Stack::render('scripts'));
	}

	public function testPushUnique(): void
	{
		Stack::push('styles', 'a', unique: true);
		Stack::push('styles', 'a', unique: true);
		Stack::push('styles', 'b', unique: true);

		$this->assertSame('a' . PHP_EOL . 'b', Stack::render('styles'));
	}

	public function testRenderWithoutClear(): void
	{
		Stack::push('styles', 'a');

		$this->assertSame('a', Stack::render('styles', clear: false));
		$this->assertSame('a', Stack::render('styles', clear: false));
	}
}
