<?php

namespace Kirby\Text\Markdown;

use Kirby\TestCase;
use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\HardBreak;
use Kirby\Text\Markdown\AST\SoftBreak;
use Kirby\Text\Markdown\AST\Text;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Inlines::class)]
class InlinesTest extends TestCase
{
	protected Inlines $inlines;

	public function setUp(): void
	{
		$this->inlines = new Inlines(new Parser());
	}

	public function testParsePlainText(): void
	{
		// a run without a single marker is one plain text node
		$nodes = $this->inlines->parse('plain text');

		$this->assertCount(1, $nodes);
		$this->assertInstanceOf(Text::class, $nodes[0]);
		$this->assertSame('plain text', $nodes[0]->text);
	}

	public function testParseSpan(): void
	{
		$nodes = $this->inlines->parse('a *b* c');

		$this->assertCount(3, $nodes);
		$this->assertInstanceOf(Text::class, $nodes[0]);
		$this->assertSame('a ', $nodes[0]->text);

		$this->assertInstanceOf(Element::class, $nodes[1]);
		$this->assertSame('em', $nodes[1]->name);
		$this->assertNull($nodes[1]->content);
		$this->assertSame('b', $nodes[1]->children[0]->text);

		$this->assertSame(' c', $nodes[2]->text);
	}

	public function testParseUnmatchedMarker(): void
	{
		// a delimiter run that pairs with nothing becomes literal text
		$nodes = $this->inlines->parse('a * b');
		$text  = implode('', array_map(fn ($node) => $node->text, $nodes));

		$this->assertContainsOnlyInstancesOf(Text::class, $nodes);
		$this->assertSame('a * b', $text);
	}

	public function testParseHardBreak(): void
	{
		// two trailing spaces before a newline become a hard break
		$nodes = $this->inlines->parse("line1  \nline2");

		$this->assertInstanceOf(Element::class, $nodes[0]);
		$this->assertNull($nodes[0]->name);
		$this->assertInstanceOf(HardBreak::class, $nodes[0]->children[1]);
	}

	public function testParseSoftBreak(): void
	{
		// a plain newline is a soft break, dropping the trailing space
		$nodes = $this->inlines->parse("line1 \nline2");

		$this->assertInstanceOf(SoftBreak::class, $nodes[0]->children[1]);
		$this->assertSame('line1', $nodes[0]->children[0]->text);
	}

	public function testParseSoftBreakWithBreaks(): void
	{
		// with breaks enabled, every plain newline becomes a hard break
		$inlines = new Inlines(new Parser(breaks: true));
		$nodes = $inlines->parse("line1\nline2");

		$this->assertInstanceOf(Element::class, $nodes[0]);
		$this->assertNull($nodes[0]->name);
		$this->assertInstanceOf(HardBreak::class, $nodes[0]->children[1]);
	}

	public function testParseLink(): void
	{
		// a `]`/`[` dispatches to the bracket resolver; the element sits
		// between the (empty) leading and trailing boundary text nodes
		// the parser keeps for the renderer's break placement
		$nodes = $this->inlines->parse('[text](/url)');
		$link  = $nodes[1];

		$this->assertInstanceOf(Element::class, $link);
		$this->assertSame('a', $link->name);
		$this->assertSame('/url', $link->attributes['href']);
	}

	public function testParseImage(): void
	{
		// a `![` opens an image bracket
		$nodes = $this->inlines->parse('![alt](/img.jpg)');
		$image = $nodes[1];

		$this->assertInstanceOf(Element::class, $image);
		$this->assertSame('img', $image->name);
	}

	public function testParseUnclaimedMarker(): void
	{
		// a `!` not followed by `[` is claimed by nothing and stays literal
		$nodes = $this->inlines->parse('a ! b');
		$text  = implode('', array_map(
			fn ($node) => $node instanceof Text ? $node->text : '',
			$nodes
		));

		$this->assertSame('a ! b', $text);
	}
}
