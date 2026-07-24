<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\HardBreak;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BackslashEscape::class)]
class BackslashEscapeTest extends TestCase
{
	protected BackslashEscape $inline;

	public function setUp(): void
	{
		$this->inline = new BackslashEscape(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(BackslashEscape::class);
	}

	public function testConsume(): void
	{
		$phrase = new Phrase('\\*rest');
		$node   = $this->inline->consume($phrase);

		// the escaped character is emitted as a non-breaking text leaf
		// (so it is HTML-escaped on render, not re-parsed as a marker)
		$this->assertInstanceOf(Text::class, $node);
		$this->assertSame('*', $node->text);
		$this->assertFalse($node->hasBreak());

		// the backslash and the escaped character are consumed
		$this->assertSame('rest', $phrase->after());
	}

	public function testConsumeHardBreak(): void
	{
		// a backslash before a newline is a hard line break
		$phrase = new Phrase("\\\nrest");
		$node   = $this->inline->consume($phrase);

		$this->assertInstanceOf(HardBreak::class, $node);
		$this->assertSame('rest', $phrase->after());
	}

	public function testConsumeNonSpecialChar(): void
	{
		// a letter is not escapable
		$phrase = new Phrase('\\a');

		$this->assertFalse($this->inline->consume($phrase));
	}

	public function testConsumeNothingAfterBackslash(): void
	{
		$phrase = new Phrase('\\');

		$this->assertFalse($this->inline->consume($phrase));
	}
}
