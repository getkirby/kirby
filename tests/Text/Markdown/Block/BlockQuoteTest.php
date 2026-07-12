<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlockQuote::class)]
class BlockQuoteTest extends TestCase
{
	protected BlockQuote $block;

	public function setUp(): void
	{
		$this->block = new BlockQuote(new Parser());
	}

	/**
	 * The resolved text of the quote's first block (its content is
	 * parsed into block nodes during `consume()`).
	 */
	protected function content(Element $node): string|null
	{
		return $node->children[0]->content ?? null;
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(BlockQuote::class);
	}

	public function testConsume(): void
	{
		$line = new Line(['> quote']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('blockquote', $node->name);
		$this->assertTrue($node->multiline);
		$this->assertTrue($node->block);
		$this->assertSame('quote', $this->content($node));
	}

	public function testConsumeMultiLine(): void
	{
		$line = new Line(['> a', '> b']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame("a\nb", $this->content($node));
	}

	public function testConsumeLazyContinuation(): void
	{
		// a line without `>` still continues the open quote
		$line = new Line(['> a', 'b']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame("a\nb", $this->content($node));
	}

	public function testConsumeLazyContinuationRejectsInterruptingBlock(): void
	{
		// a line that starts a new block (here a thematic break) is not
		// paragraph-continuation text, so it closes the quote
		$line = new Line(['> foo', '---']);
		$node = $this->block->consume($line);

		$this->assertSame('foo', $this->content($node));
		$this->assertTrue($line->matches('/^---/'));
	}

	public function testConsumeLazyContinuationRequiresParagraph(): void
	{
		// a line without `>` cannot continue a non-paragraph block: the
		// quote holds a list, so `- bar` closes it
		$line = new Line(['> - foo', '- bar']);
		$node = $this->block->consume($line);

		$this->assertSame('ul', $node->children[0]->name);
		$this->assertTrue($line->matches('/^- bar/'));
	}

	public function testConsumeBlankLineCloses(): void
	{
		// a blank line ends the quote and is left for the next parser
		$line = new Line(['> a', '', '> b']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('a', $this->content($node));
		$this->assertTrue($line->isBlank());
	}

	public function testConsumeWithoutSpace(): void
	{
		// only a single space after the marker is stripped
		$line = new Line(['>a']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('a', $this->content($node));
	}
}
