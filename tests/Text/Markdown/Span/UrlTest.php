<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Url::class)]
class UrlTest extends TestCase
{
	protected Url $span;

	public function setUp(): void
	{
		$this->span = new Url(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Url::class);
	}

	public function testConsume(): void
	{
		$phrase = new Phrase('see https://example.com');
		$phrase->seek(':');
		$node = $this->span->consume($phrase);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('a', $node->name);
		$this->assertSame(['href' => 'https://example.com'], $node->attributes);
		$this->assertFalse($node->hasBreak());

		$this->assertInstanceOf(Text::class, $node->children[0]);
		$this->assertSame('https://example.com', $node->children[0]->text);

		// the mark reaches back to the start of the URL, before the marker
		$this->assertSame('see ', $phrase->lead());
		$this->assertSame(19, $phrase->consumed());
	}

	public function testConsumeNotDoubleSlash(): void
	{
		// the character two past the colon must be a slash
		$phrase = new Phrase('a:bc');
		$phrase->seek(':');

		$this->assertFalse($this->span->consume($phrase));
	}

	public function testConsumeNonHttpScheme(): void
	{
		// only http(s) URLs are autolinked
		$phrase = new Phrase('ftp://example.com');
		$phrase->seek(':');

		$this->assertFalse($this->span->consume($phrase));
	}
}
