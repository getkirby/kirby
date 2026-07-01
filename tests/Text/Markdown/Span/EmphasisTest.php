<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Emphasis::class)]
class EmphasisTest extends TestCase
{
	protected Emphasis $span;

	public function setUp(): void
	{
		$this->span = new Emphasis(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Emphasis::class);
	}

	public function testConsumeEmAsterisk(): void
	{
		$phrase = new Phrase('*em*');
		$node   = $this->span->consume($phrase);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('em', $node->name);
		$this->assertSame('em', $node->content);
		$this->assertTrue($node->multiline);
		$this->assertFalse($node->hasBreak());
	}

	public function testConsumeEmUnderscore(): void
	{
		$phrase = new Phrase('_em_');
		$node   = $this->span->consume($phrase);

		$this->assertSame('em', $node->name);
		$this->assertSame('em', $node->content);
	}

	public function testConsumeStrongAsterisk(): void
	{
		$phrase = new Phrase('**strong**');
		$node   = $this->span->consume($phrase);

		$this->assertSame('strong', $node->name);
		$this->assertSame('strong', $node->content);
	}

	public function testConsumeStrongUnderscore(): void
	{
		$phrase = new Phrase('__strong__');
		$node   = $this->span->consume($phrase);

		$this->assertSame('strong', $node->name);
		$this->assertSame('strong', $node->content);
	}

	public function testConsumeNothingAfter(): void
	{
		$phrase = new Phrase('*');

		$this->assertFalse($this->span->consume($phrase));
	}

	public function testConsumeUnclosed(): void
	{
		$phrase = new Phrase('*not closed');

		$this->assertFalse($this->span->consume($phrase));
	}
}
