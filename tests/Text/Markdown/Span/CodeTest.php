<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Code::class)]
class CodeTest extends TestCase
{
	protected Code $span;

	public function setUp(): void
	{
		$this->span = new Code(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Code::class);
	}

	public function testConsume(): void
	{
		$phrase = new Phrase('`code`');
		$node   = $this->span->consume($phrase);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('code', $node->name);
		$this->assertFalse($node->hasBreak());

		$this->assertInstanceOf(Text::class, $node->children[0]);
		$this->assertSame('code', $node->children[0]->text);

		$this->assertSame(6, $phrase->consumed());
	}

	public function testConsumeTrimsSurroundingSpaces(): void
	{
		// a single leading and trailing space is stripped
		$phrase = new Phrase('` code `');
		$node   = $this->span->consume($phrase);

		$this->assertSame('code', $node->children[0]->text);
	}

	public function testConsumeMultipleBackticks(): void
	{
		// a doubled fence lets the content contain a single backtick
		$phrase = new Phrase('``a`b``');
		$node   = $this->span->consume($phrase);

		$this->assertSame('a`b', $node->children[0]->text);
	}

	public function testConsumeUnclosed(): void
	{
		$phrase = new Phrase('`unclosed');

		$this->assertFalse($this->span->consume($phrase));
	}
}
