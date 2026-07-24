<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\HardBreak;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Image::class)]
class ImageTest extends TestCase
{
	protected Parser $parser;
	protected Image $inline;

	public function setUp(): void
	{
		$this->parser = new Parser();
		$this->inline = new Image($this->parser);
	}

	/**
	 * The first `<img>` element in the parsed inlines, or `null` when the
	 * text produced no image.
	 */
	protected function image(string $text): Element|null
	{
		foreach ($this->parser->inlines()->parse($text) as $node) {
			if ($node instanceof Element && $node->name === 'img') {
				return $node;
			}
		}

		return null;
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Image::class);
	}

	public function testAlt(): void
	{
		// the alt text is the plain text of the label, markup dropped
		$nodes = [
			new Text('a '),
			new Element(name: 'em', children: [new Text('b')]),
			new Element(name: 'code', children: [new Text('c')])
		];

		$this->assertSame('a bc', Image::alt($nodes));
	}

	public function testAltNestedImage(): void
	{
		// a nested image contributes its own alt text
		$nodes = [
			new Element(name: 'img', attributes: ['alt' => 'inner'])
		];

		$this->assertSame('inner', Image::alt($nodes));
	}

	public function testConsumeDeclines(): void
	{
		// a `!` is resolved by the delimiter stack, never by dispatch
		$this->assertFalse($this->inline->consume(new Phrase('![alt](/img.jpg)')));
	}

	public function testParse(): void
	{
		$node = $this->image('![Alt text](/path/to/img.jpg)');

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('img', $node->name);
		$this->assertSame('/path/to/img.jpg', $node->attributes['src']);
		$this->assertSame('Alt text', $node->attributes['alt']);
		$this->assertNull($node->attributes['title']);
		$this->assertTrue($node->hasBreak());
	}

	public function testParseWithTitle(): void
	{
		$node = $this->image('![Alt text](/path/to/img.jpg "Optional title")');

		$this->assertSame('Optional title', $node->attributes['title']);
	}

	public function testParseWithAttributes(): void
	{
		// a trailing attribute block is carried onto the image
		$node = $this->image('![Alt](/img.jpg){.big}');

		$this->assertSame('/img.jpg', $node->attributes['src']);
		$this->assertSame('big', $node->attributes['class']);
	}

	public function testParseReference(): void
	{
		$this->parser->data()->set('LinkDefinition', 'id', [
			'url'   => '/path/to/img.jpg',
			'title' => null
		]);

		$node = $this->image('![Alt text][id]');

		$this->assertSame('/path/to/img.jpg', $node->attributes['src']);
		$this->assertSame('Alt text', $node->attributes['alt']);
	}

	public function testParseNestedLink(): void
	{
		// an image label may contain a link; it becomes plain alt text
		$node = $this->image('![foo [bar](/url)](/img.jpg)');

		$this->assertSame('/img.jpg', $node->attributes['src']);
		$this->assertSame('foo bar', $node->attributes['alt']);
	}

	public function testParseUndefinedReference(): void
	{
		// a reference-style image without a matching definition is not one
		$this->assertNull($this->image('![Alt text][missing]'));
	}

	public function testParseNoBracket(): void
	{
		// the `!` must be immediately followed by a bracket
		$this->assertNull($this->image('!not an image'));
	}

	public function testAltFromNestedNodes(): void
	{
		// a nested image contributes its own alt text, any other element
		// its recursively collected text, and anything else (here a hard
		// break) nothing
		$alt = Image::alt([
			new Text('a '),
			new Element(name: 'img', attributes: ['alt' => 'b']),
			new Element(name: 'em', children: [new Text(' c')]),
			new HardBreak()
		]);

		$this->assertSame('a b c', $alt);
	}
}
