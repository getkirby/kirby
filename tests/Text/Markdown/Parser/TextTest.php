<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\TestCase;
use Kirby\Text\Markdown\AST;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Text::class)]
class TextTest extends TestCase
{
	public function testBuildPlain(): void
	{
		// a run without a line break is a single text leaf
		$node = (new Text(breaks: false))->build('plain text');

		$this->assertInstanceOf(AST\Text::class, $node);
		$this->assertSame('plain text', $node->text);
	}

	public function testBuildSoftBreak(): void
	{
		// a plain newline becomes a soft break, dropping the trailing space
		$node = (new Text(breaks: false))->build("line1 \nline2");

		$this->assertInstanceOf(AST\Element::class, $node);
		$this->assertNull($node->name);
		$this->assertSame('line1', $node->children[0]->text);
		$this->assertInstanceOf(AST\SoftBreak::class, $node->children[1]);
		$this->assertSame('line2', $node->children[2]->text);
	}

	public function testBuildHardBreak(): void
	{
		// two or more trailing spaces make the break hard
		$node = (new Text(breaks: false))->build("line1  \nline2");
		$this->assertInstanceOf(AST\HardBreak::class, $node->children[1]);
	}

	public function testBuildBreaksMode(): void
	{
		// with breaks enabled every soft break becomes hard
		$node = (new Text(breaks: true))->build("line1\nline2");
		$this->assertInstanceOf(AST\HardBreak::class, $node->children[1]);
	}

	public function testBuildDropsEmptySegments(): void
	{
		// a blank first line and a blank final line emit no text leaf,
		// only the break between them
		$node = (new Text(breaks: false))->build("\n");

		$this->assertInstanceOf(AST\Element::class, $node);
		$this->assertCount(1, $node->children);
		$this->assertInstanceOf(AST\SoftBreak::class, $node->children[0]);
	}
}
