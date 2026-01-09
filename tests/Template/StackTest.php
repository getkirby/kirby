<?php

namespace Kirby\Template;

use Kirby\Toolkit\Tpl;
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
		$this->assertSame('HelloHello', $output);
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
		$output = Tpl::load(static::FIXTURES . '/stack-order.php');
		$this->assertSame('Hello', trim($output));
	}

	public function testEndWithoutBegin(): void
	{
		Stack::end();
		$this->assertSame('', Stack::render('missing'));
	}

	public function testOpenClose(): void
	{
		$this->assertFalse(Stack::isOpen());

		Stack::open();
		$this->assertTrue(Stack::isOpen());

		Stack::open();
		$this->assertTrue(Stack::isOpen());

		Stack::close();
		$this->assertTrue(Stack::isOpen());

		Stack::close();
		$this->assertFalse(Stack::isOpen());
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

		$this->assertSame('ab', Stack::render('scripts'));
		$this->assertSame('', Stack::render('scripts'));
	}

	public function testPushUnique(): void
	{
		Stack::push('styles', 'a', unique: true);
		Stack::push('styles', 'a', unique: true);
		Stack::push('styles', 'b', unique: true);

		$this->assertSame('ab', Stack::render('styles'));
	}

	public function testRenderWithoutClear(): void
	{
		Stack::push('styles', 'a');

		$this->assertSame('a', Stack::render('styles', clear: false));
		$this->assertSame('a', Stack::render('styles', clear: false));
	}

}
