<?php

namespace Kirby\Text\Markdown;

use Kirby\TestCase;
use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Span\Code;
use Kirby\Text\Markdown\Span\Emphasis;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Spans::class)]
class SpansTest extends TestCase
{
	protected Spans $spans;

	public function setUp(): void
	{
		$this->spans = new Spans(new Parser());
	}

	public function testParsePlainText(): void
	{
		// a run without a single marker is one plain text node
		$nodes = $this->spans->parse('plain text');

		$this->assertCount(1, $nodes);
		$this->assertInstanceOf(Text::class, $nodes[0]);
		$this->assertSame('plain text', $nodes[0]->text);
	}

	public function testParseSpan(): void
	{
		$nodes = $this->spans->parse('a *b* c');

		$this->assertCount(3, $nodes);
		$this->assertInstanceOf(Text::class, $nodes[0]);
		$this->assertSame('a ', $nodes[0]->text);

		$this->assertInstanceOf(Element::class, $nodes[1]);
		$this->assertSame('em', $nodes[1]->name);
		$this->assertSame('b', $nodes[1]->content);

		$this->assertSame(' c', $nodes[2]->text);
	}

	public function testParseDisabled(): void
	{
		// with emphasis disabled, no element is produced
		$nodes    = $this->spans->parse('a *b* c', disabled: [Emphasis::class]);
		$elements = array_filter($nodes, fn ($n) => $n instanceof Element);

		$this->assertSame([], $elements);
	}

	public function testParseDisabledInherited(): void
	{
		// a non-empty disabled list that does not match the produced
		// span still parses it, and the element inherits the disabled
		// types as omitted span types for its deferred content
		$nodes = $this->spans->parse('a *b* c', disabled: [Code::class]);

		$this->assertInstanceOf(Element::class, $nodes[1]);
		$this->assertSame('em', $nodes[1]->name);
		$this->assertSame([Code::class], $nodes[1]->omit);
	}

	public function testParseUnmatchedMarker(): void
	{
		// a marker without a matching span is skipped as plain text
		$nodes = $this->spans->parse('a * b');

		$this->assertCount(2, $nodes);
		$this->assertInstanceOf(Text::class, $nodes[0]);
		$this->assertInstanceOf(Text::class, $nodes[1]);
		$this->assertSame('a * b', $nodes[0]->text . $nodes[1]->text);
	}

	public function testParseHardBreak(): void
	{
		// two trailing spaces before a newline become a <br>
		$nodes = $this->spans->parse("line1  \nline2");

		$this->assertInstanceOf(Element::class, $nodes[0]);
		$this->assertNull($nodes[0]->name);
		$this->assertSame('br', $nodes[0]->children[1]->name);
	}

	public function testParseSoftBreakWithBreaks(): void
	{
		// with breaks enabled, a plain newline is split on `\n`
		$spans = new Spans(new Parser(breaks: true));
		$nodes = $spans->parse("line1\nline2");

		$this->assertInstanceOf(Element::class, $nodes[0]);
		$this->assertNull($nodes[0]->name);
		$this->assertSame('br', $nodes[0]->children[1]->name);
	}

	public function testReplace(): void
	{
		$element = new Element(name: 'x');
		$nodes   = Spans::replace('/b/', [$element], 'abc');

		$this->assertCount(3, $nodes);
		$this->assertSame('a', $nodes[0]->text);
		$this->assertSame($element, $nodes[1]);
		$this->assertSame('c', $nodes[2]->text);
	}
}
