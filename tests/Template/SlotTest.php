<?php

namespace Kirby\Template;

use Kirby\Exception\LogicException;

class SlotTest extends TestCase
{
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

	public function testSlot()
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

	public function testCloseWhenNotOpen()
	{
		$slot = new Slot(new Snippet('test.php'), 'test');

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The slot has not been opened');

		$slot->close();
	}
}
