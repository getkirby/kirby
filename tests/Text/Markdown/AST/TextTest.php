<?php

namespace Kirby\Text\Markdown\AST;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Text::class)]
#[CoversClass(Node::class)]
class TextTest extends TestCase
{
	public function testConstruct(): void
	{
		$text = new Text('hello');
		$this->assertSame('hello', $text->text);
		$this->assertNull($text->break);
	}

	public function testHasBreak(): void
	{
		// a leaf does not break by default
		$this->assertFalse((new Text('x'))->hasBreak());

		$this->assertTrue((new Text('x', break: true))->hasBreak());
	}
}
