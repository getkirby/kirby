<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Markup::class)]
class MarkupTest extends TestCase
{
	protected Markup $span;

	public function setUp(): void
	{
		$this->span = new Markup(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Markup::class);
	}

	public function testConsumeOpeningTag(): void
	{
		$phrase = new Phrase('<span>rest');
		$node   = $this->span->consume($phrase);

		$this->assertInstanceOf(Html::class, $node);
		$this->assertSame('<span>', $node->html);
		$this->assertFalse($node->hasBreak());
		$this->assertSame('rest', $phrase->after());
	}

	public function testConsumeClosingTag(): void
	{
		$phrase = new Phrase('</span>');
		$node   = $this->span->consume($phrase);

		$this->assertInstanceOf(Html::class, $node);
		$this->assertSame('</span>', $node->html);
	}

	public function testConsumeSelfClosingTag(): void
	{
		$phrase = new Phrase('<br/>');
		$node   = $this->span->consume($phrase);

		$this->assertSame('<br/>', $node->html);
	}

	public function testConsumeComment(): void
	{
		$phrase = new Phrase('<!-- hi -->');
		$node   = $this->span->consume($phrase);

		$this->assertInstanceOf(Html::class, $node);
		$this->assertSame('<!-- hi -->', $node->html);
	}

	public function testConsumeTagWithAttributes(): void
	{
		$phrase = new Phrase('<a href="#">');
		$node   = $this->span->consume($phrase);

		$this->assertSame('<a href="#">', $node->html);
	}

	public function testConsumeSafeMode(): void
	{
		// in safe mode raw HTML is never consumed
		$span   = new Markup(new Parser(safe: true));
		$phrase = new Phrase('<span>');

		$this->assertFalse($span->consume($phrase));
	}

	public function testConsumeUnclosed(): void
	{
		$phrase = new Phrase('<span');

		$this->assertFalse($this->span->consume($phrase));
	}

	public function testConsumeSpaceAfterBracket(): void
	{
		// `< span>` is not a tag
		$phrase = new Phrase('< span>');

		$this->assertFalse($this->span->consume($phrase));
	}
}
