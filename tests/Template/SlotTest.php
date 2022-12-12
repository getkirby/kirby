<?php

namespace Kirby\Template;

use Kirby\Exception\LogicException;

/**
 * @coversDefaultClass Kirby\Template\Slot
 */
class SlotTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$snippet = new Snippet('test.php');
		$slot    = new Slot($snippet, 'test');

		$this->assertSame($snippet, $slot->snippet);
		$this->assertSame('test', $slot->name);
		$this->assertFalse($slot->open);
		$this->assertNull($slot->content);
		$this->assertNull($slot->render());
		$this->assertSame('', $slot->__toString());
	}

	/**
	 * @covers ::begin
	 * @covers ::end
	 */
	public function testHelpers()
	{
		$this->assertNull(Snippet::$current);
		$snippet = Snippet::begin('test.php');
		$this->assertSame($snippet, Snippet::$current);

		$this->assertCount(0, $snippet->capture);
		$this->assertCount(0, $snippet->slots);

		$slot = Slot::begin();
		$this->assertInstanceOf(Slot::class, $slot);
		$this->assertCount(1, $snippet->capture);
		$this->assertCount(0, $snippet->slots);

		Slot::end();
		$this->assertCount(0, $snippet->capture);
		$this->assertCount(1, $snippet->slots);

		$snippet->close();
	}

	/**
	 * @covers ::open
	 * @covers ::close
	 */
	public function testOpenClose()
	{
		$slot = new Slot(new Snippet('test.php'), 'test');

		$this->assertNull($slot->content);
		$this->assertFalse($slot->open);
		$slot->open();
		$this->assertTrue($slot->open);

		echo $content = 'Test';
		$slot->close();

		$this->assertFalse($slot->open);
		$this->assertSame($content, $slot->content);
	}

	/**
	 * @covers ::close
	 */
	public function testCloseWhenNotOpen()
	{
		$slot = new Slot(new Snippet('test.php'), 'test');

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The slot has not been opened');

		$slot->close();
	}

	/**
	 * @covers ::render
	 * @covers ::__toString
	 */
	public function testRender()
	{
		$slot    = new Slot(new Snippet('test.php'), 'test');
		$content = 'Test content';

		$slot->open();
		echo $content;
		$slot->close();

		$this->assertSame('Test content', $slot->content);
		$this->assertSame('Test content', $slot->render());
		$this->assertSame('Test content', $slot->__toString());
	}
}
