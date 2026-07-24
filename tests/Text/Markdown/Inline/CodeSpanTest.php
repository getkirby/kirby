<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeSpan::class)]
class CodeSpanTest extends TestCase
{
	protected CodeSpan $inline;

	public function setUp(): void
	{
		$this->inline = new CodeSpan(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(CodeSpan::class);
	}

	public function testConsume(): void
	{
		$phrase = new Phrase('`code`');
		$node   = $this->inline->consume($phrase);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('code', $node->name);
		$this->assertFalse($node->hasBreak());

		$this->assertInstanceOf(Text::class, $node->children[0]);
		$this->assertSame('code', $node->children[0]->text);

		$this->assertSame('', $phrase->after());
	}

	public function testConsumeTrimsSurroundingSpaces(): void
	{
		// a single leading and trailing space is stripped
		$phrase = new Phrase('` code `');
		$node   = $this->inline->consume($phrase);

		$this->assertSame('code', $node->children[0]->text);
	}

	public function testConsumeKeepsUnpaddedSpace(): void
	{
		// only strips when both ends are padded: `` ` a` `` keeps its space
		$phrase = new Phrase('` a`');
		$node   = $this->inline->consume($phrase);

		$this->assertSame(' a', $node->children[0]->text);
	}

	public function testConsumeKeepsInnerBackticksWhenPadded(): void
	{
		// stripping one space each side can expose content that is itself
		// backticks
		$phrase = new Phrase('`  ``  `');
		$node   = $this->inline->consume($phrase);

		$this->assertSame(' `` ', $node->children[0]->text);
	}

	public function testConsumeMultipleBackticks(): void
	{
		// a doubled fence lets the content contain a single backtick
		$phrase = new Phrase('``a`b``');
		$node   = $this->inline->consume($phrase);

		$this->assertSame('a`b', $node->children[0]->text);
	}

	public function testConsumeUnclosed(): void
	{
		// an opening backtick run with no matching close is literal as a
		// whole (so the scanner does not open a shorter span inside it)
		$phrase = new Phrase('```unclosed');
		$node   = $this->inline->consume($phrase);

		$this->assertInstanceOf(Text::class, $node);
		$this->assertSame('```', $node->text);
	}
}
