<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Image::class)]
class ImageTest extends TestCase
{
	protected Parser $parser;
	protected Image $span;

	public function setUp(): void
	{
		$this->parser = new Parser();
		$this->span   = new Image($this->parser);
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Image::class);
	}

	public function testConsume(): void
	{
		$phrase = new Phrase('![Alt text](/path/to/img.jpg)');
		$node   = $this->span->consume($phrase);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('img', $node->name);
		$this->assertSame('/path/to/img.jpg', $node->attributes['src']);
		$this->assertSame('Alt text', $node->attributes['alt']);
		$this->assertNull($node->attributes['title']);
		$this->assertTrue($node->hasBreak());
	}

	public function testConsumeWithTitle(): void
	{
		$phrase = new Phrase('![Alt text](/path/to/img.jpg "Optional title")');
		$node   = $this->span->consume($phrase);

		$this->assertSame('Optional title', $node->attributes['title']);
	}

	public function testConsumeWithAttributes(): void
	{
		// a trailing attribute block is carried onto the image
		$phrase = new Phrase('![Alt](/img.jpg){.big}');
		$node   = $this->span->consume($phrase);

		$this->assertSame('/img.jpg', $node->attributes['src']);
		$this->assertSame('big', $node->attributes['class']);
	}

	public function testConsumeReference(): void
	{
		$this->parser->data()->set('Reference', 'id', [
			'url'   => '/path/to/img.jpg',
			'title' => null
		]);

		$phrase = new Phrase('![Alt text][id]');
		$node   = $this->span->consume($phrase);

		$this->assertSame('/path/to/img.jpg', $node->attributes['src']);
		$this->assertSame('Alt text', $node->attributes['alt']);
	}

	public function testConsumeUndefinedReference(): void
	{
		// a reference-style image without a matching definition
		// falls through the inner link parser and is not an image
		$phrase = new Phrase('![Alt text][missing]');

		$this->assertFalse($this->span->consume($phrase));
	}

	public function testConsumeNoBracket(): void
	{
		// the `!` must be immediately followed by a bracket
		$phrase = new Phrase('!not an image');

		$this->assertFalse($this->span->consume($phrase));
	}
}
