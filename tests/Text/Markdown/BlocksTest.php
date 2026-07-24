<?php

namespace Kirby\Text\Markdown;

use Kirby\TestCase;
use Kirby\Text\Markdown\AST\Element;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Blocks::class)]
class BlocksTest extends TestCase
{
	protected Blocks $blocks;

	public function setUp(): void
	{
		$this->blocks = new Blocks(new Parser());
	}

	public function testParseParagraph(): void
	{
		$nodes = $this->blocks->parse('Hello world');

		$this->assertCount(1, $nodes);
		$this->assertInstanceOf(Element::class, $nodes[0]);
		$this->assertSame('p', $nodes[0]->name);

		// content is left deferred for the resolver
		$this->assertSame('Hello world', $nodes[0]->content);
	}

	public function testParseMultipleParagraphs(): void
	{
		$nodes = $this->blocks->parse("One\n\nTwo");

		$this->assertCount(2, $nodes);
		$this->assertSame('One', $nodes[0]->content);
		$this->assertSame('Two', $nodes[1]->content);
	}

	public function testParseLazyContinuation(): void
	{
		// consecutive non-blank lines join one paragraph
		$nodes = $this->blocks->parse("One\nTwo");

		$this->assertCount(1, $nodes);
		$this->assertSame("One\nTwo", $nodes[0]->content);
	}

	public function testParseBlock(): void
	{
		$nodes = $this->blocks->parse('# Title');

		$this->assertSame('h1', $nodes[0]->name);
		$this->assertSame('Title', $nodes[0]->content);
	}

	public function testParseBlockAfterParagraph(): void
	{
		// a fresh block flushes the open paragraph before its own node
		$nodes = $this->blocks->parse("Hello\n# Title");

		$this->assertCount(2, $nodes);
		$this->assertSame('p', $nodes[0]->name);
		$this->assertSame('Hello', $nodes[0]->content);
		$this->assertSame('h1', $nodes[1]->name);
		$this->assertSame('Title', $nodes[1]->content);
	}

	public function testParseIndentedCode(): void
	{
		// a 4-space indent dispatches to the indented code block
		$nodes = $this->blocks->parse('    $foo = 1;');

		$this->assertCount(1, $nodes);
		$this->assertSame('pre', $nodes[0]->name);
	}

	public function testParseIndentedContinuation(): void
	{
		// an indented line must not interrupt a running paragraph:
		// the indented code block declines and the text joins the paragraph
		$nodes = $this->blocks->parse("Hello\n    world");

		$this->assertCount(1, $nodes);
		$this->assertSame('p', $nodes[0]->name);
		$this->assertSame("Hello\nworld", $nodes[0]->content);
	}

	public function testParseIndentedMarkerIsCode(): void
	{
		// at four spaces of indent only indented code can start, never a
		// marker-based block (here `#` is code, not a heading)
		$nodes = $this->blocks->parse('    # not a heading');

		$this->assertCount(1, $nodes);
		$this->assertSame('pre', $nodes[0]->name);
	}

	public function testParseIndentedMarkerContinuesParagraph(): void
	{
		// and with a paragraph open, that same line is a lazy continuation
		$nodes = $this->blocks->parse("foo\n    # not a heading");

		$this->assertCount(1, $nodes);
		$this->assertSame('p', $nodes[0]->name);
		$this->assertSame("foo\n# not a heading", $nodes[0]->content);
	}

	public function testItem(): void
	{
		// a list item's blocks are parsed and reported as tight when no
		// blank line separates them
		[$nodes, $loose] = $this->blocks->item(['text']);

		$this->assertSame('p', $nodes[0]->name);
		$this->assertFalse($loose);
	}

	public function testItemLoose(): void
	{
		// a blank line between two top-level blocks makes the item loose
		[$nodes, $loose] = $this->blocks->item(['a', '', 'b']);

		$this->assertCount(2, $nodes);
		$this->assertTrue($loose);
	}

	public function testItemNestedBlankIsNotLoose(): void
	{
		// a blank line consumed by a nested block (here a fenced code
		// block) does not reach the top level, so the item stays tight
		[, $loose] = $this->blocks->item(['```', 'a', '', 'b', '```']);

		$this->assertFalse($loose);
	}

	public function testParseArraySource(): void
	{
		// the source may be given as an array of lines instead of a string
		$nodes = $this->blocks->parse(['One', '', 'Two']);

		$this->assertCount(2, $nodes);
	}
}
