<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Quote::class)]
class QuoteTest extends TestCase
{
	protected Quote $block;

	public function setUp(): void
	{
		$this->block = new Quote(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Quote::class);
	}

	public function testConsume(): void
	{
		$line = new Line(['> quote']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('blockquote', $node->name);
		$this->assertTrue($node->multiline);
		$this->assertTrue($node->block);
		$this->assertSame(['quote'], $node->content);
	}

	public function testConsumeMultiLine(): void
	{
		$line = new Line(['> a', '> b']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame(['a', 'b'], $node->content);
	}

	public function testConsumeLazyContinuation(): void
	{
		// a line without `>` still continues the open quote
		$line = new Line(['> a', 'b']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame(['a', 'b'], $node->content);
	}

	public function testConsumeBlankLineCloses(): void
	{
		// a blank line ends the quote and is left for the next parser
		$line = new Line(['> a', '', '> b']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame(['a'], $node->content);
		$this->assertTrue($line->isBlank());
	}

	public function testConsumeWithoutSpace(): void
	{
		// only a single space after the marker is stripped
		$line = new Line(['>a']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame(['a'], $node->content);
	}
}
