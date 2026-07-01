<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UrlTag::class)]
class UrlTagTest extends TestCase
{
	protected UrlTag $span;

	public function setUp(): void
	{
		$this->span = new UrlTag(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(UrlTag::class);
	}

	public function testConsume(): void
	{
		$phrase = new Phrase('<https://example.com>');
		$node   = $this->span->consume($phrase);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('a', $node->name);
		$this->assertSame(['href' => 'https://example.com'], $node->attributes);
		$this->assertInstanceOf(Text::class, $node->children[0]);
		$this->assertSame('https://example.com', $node->children[0]->text);
		$this->assertFalse($node->hasBreak());
	}

	public function testConsumeUnclosed(): void
	{
		$phrase = new Phrase('<https://example.com');

		$this->assertFalse($this->span->consume($phrase));
	}

	public function testConsumeNotAUrl(): void
	{
		// no scheme, so not an autolinked URL tag
		$phrase = new Phrase('<not a url>');

		$this->assertFalse($this->span->consume($phrase));
	}
}
