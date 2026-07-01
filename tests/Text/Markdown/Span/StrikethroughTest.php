<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Strikethrough::class)]
class StrikethroughTest extends TestCase
{
	protected Strikethrough $span;

	public function setUp(): void
	{
		$this->span = new Strikethrough(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Strikethrough::class);
	}

	public function testConsume(): void
	{
		$phrase = new Phrase('~~struck~~');
		$node   = $this->span->consume($phrase);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('del', $node->name);
		$this->assertSame('struck', $node->content);
		$this->assertTrue($node->multiline);
		$this->assertFalse($node->hasBreak());
		$this->assertSame(10, $phrase->consumed());
	}

	public function testConsumeSingleTilde(): void
	{
		// a single tilde is not a strikethrough
		$phrase = new Phrase('~x~');

		$this->assertFalse($this->span->consume($phrase));
	}

	public function testConsumeWhitespaceOnly(): void
	{
		// the content must start and end with a non-space character
		$phrase = new Phrase('~~ ~~');

		$this->assertFalse($this->span->consume($phrase));
	}

	public function testConsumeNothingAfter(): void
	{
		$phrase = new Phrase('~');

		$this->assertFalse($this->span->consume($phrase));
	}
}
