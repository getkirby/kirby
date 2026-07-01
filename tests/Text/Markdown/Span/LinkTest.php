<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Link::class)]
class LinkTest extends TestCase
{
	protected Parser $parser;
	protected Link $span;

	public function setUp(): void
	{
		$this->parser = new Parser();
		$this->span   = new Link($this->parser);
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Link::class);
	}

	public function testConsumeInline(): void
	{
		$phrase = new Phrase('[text](http://example.com/ "Title")');
		$node   = $this->span->consume($phrase);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('a', $node->name);
		$this->assertSame('text', $node->content);
		$this->assertSame('http://example.com/', $node->attributes['href']);
		$this->assertSame('Title', $node->attributes['title']);
		$this->assertFalse($node->hasBreak());
	}

	public function testConsumeInlineWithoutTitle(): void
	{
		$phrase = new Phrase('[text](http://example.net/)');
		$node   = $this->span->consume($phrase);

		$this->assertSame('http://example.net/', $node->attributes['href']);
		$this->assertNull($node->attributes['title']);
	}

	public function testConsumeInlineWithAttributes(): void
	{
		$phrase = new Phrase('[text](http://example.net/){#id .class}');
		$node   = $this->span->consume($phrase);

		$this->assertSame('http://example.net/', $node->attributes['href']);
		$this->assertSame('id', $node->attributes['id']);
		$this->assertSame('class', $node->attributes['class']);
	}

	public function testConsumeReference(): void
	{
		$this->parser->data()->set('Reference', 'id', [
			'url'   => 'http://example.com/',
			'title' => 'Title'
		]);

		$phrase = new Phrase('[text][id]');
		$node   = $this->span->consume($phrase);

		$this->assertSame('http://example.com/', $node->attributes['href']);
		$this->assertSame('Title', $node->attributes['title']);
	}

	public function testConsumeImplicitReference(): void
	{
		// an empty second bracket falls back to the link text as the id
		$this->parser->data()->set('Reference', 'text', [
			'url'   => 'http://example.com/',
			'title' => null
		]);

		$phrase = new Phrase('[text][]');
		$node   = $this->span->consume($phrase);

		$this->assertSame('http://example.com/', $node->attributes['href']);
	}

	public function testConsumeShorthandReference(): void
	{
		// a bare `[text]` with no following brackets uses the
		// link text itself as the reference id
		$this->parser->data()->set('Reference', 'text', [
			'url'   => 'http://example.com/',
			'title' => null
		]);

		$phrase = new Phrase('[text]');
		$node   = $this->span->consume($phrase);

		$this->assertSame('http://example.com/', $node->attributes['href']);
	}

	public function testConsumeUnknownReference(): void
	{
		// a reference without a matching definition is not a link
		$phrase = new Phrase('[text][missing]');

		$this->assertFalse($this->span->consume($phrase));
	}

	public function testConsumeNoLinkText(): void
	{
		$phrase = new Phrase('[no closing bracket');

		$this->assertFalse($this->span->consume($phrase));
	}
}
