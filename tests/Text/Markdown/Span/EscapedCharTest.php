<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EscapedChar::class)]
class EscapedCharTest extends TestCase
{
	protected EscapedChar $span;

	public function setUp(): void
	{
		$this->span = new EscapedChar(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(EscapedChar::class);
	}

	public function testConsume(): void
	{
		$phrase = new Phrase('\\*rest');
		$node   = $this->span->consume($phrase);

		// the escaped character is emitted as a raw, non-breaking leaf
		$this->assertInstanceOf(Html::class, $node);
		$this->assertSame('*', $node->html);
		$this->assertFalse($node->hasBreak());

		// the backslash and the escaped character are consumed
		$this->assertSame(2, $phrase->consumed());
		$this->assertSame('rest', $phrase->after());
	}

	public function testConsumeNonSpecialChar(): void
	{
		// a letter is not escapable
		$phrase = new Phrase('\\a');

		$this->assertFalse($this->span->consume($phrase));
	}

	public function testConsumeNothingAfterBackslash(): void
	{
		$phrase = new Phrase('\\');

		$this->assertFalse($this->span->consume($phrase));
	}
}
