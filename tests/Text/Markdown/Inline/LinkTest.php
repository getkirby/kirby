<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Link::class)]
class LinkTest extends TestCase
{
	protected Parser $parser;
	protected Link $inline;

	public function setUp(): void
	{
		$this->parser = new Parser();
		$this->inline = new Link($this->parser);
	}

	/**
	 * The first `<a>` element in the parsed inlines, or `null` when the
	 * text produced no link.
	 */
	protected function link(string $text): Element|null
	{
		foreach ($this->parser->inlines()->parse($text) as $node) {
			if ($node instanceof Element && $node->name === 'a') {
				return $node;
			}
		}

		return null;
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Link::class);
	}

	public function testConsumeDeclines(): void
	{
		// a `[` is resolved by the delimiter stack, never by dispatch
		$this->assertFalse($this->inline->consume(new Phrase('[text](/uri)')));
	}

	public function testOpenInline(): void
	{
		$open = $this->inline->open('(http://example.com/ "Title")', 'text');

		$this->assertSame('http://example.com/', $open['attributes']['href']);
		$this->assertSame('Title', $open['attributes']['title']);
		$this->assertSame(strlen('(http://example.com/ "Title")'), $open['length']);
	}

	public function testOpenInlineWithoutTitle(): void
	{
		$open = $this->inline->open('(http://example.net/)', 'text');

		$this->assertSame('http://example.net/', $open['attributes']['href']);
		$this->assertNull($open['attributes']['title']);
	}

	public function testOpenInlineWithAttributes(): void
	{
		$open = $this->inline->open('(http://example.net/){#id .class}', 'text');

		$this->assertSame('http://example.net/', $open['attributes']['href']);
		$this->assertSame('id', $open['attributes']['id']);
		$this->assertSame('class', $open['attributes']['class']);
		$this->assertSame(strlen('(http://example.net/){#id .class}'), $open['length']);
	}

	public function testOpenReference(): void
	{
		$this->parser->data()->set('LinkDefinition', 'id', [
			'url'   => 'http://example.com/',
			'title' => 'Title'
		]);

		$open = $this->inline->open('[id]', 'text');

		$this->assertSame('http://example.com/', $open['attributes']['href']);
		$this->assertSame('Title', $open['attributes']['title']);
		$this->assertSame(strlen('[id]'), $open['length']);
	}

	public function testOpenCollapsedReference(): void
	{
		// an empty second bracket falls back to the label as the id
		$this->parser->data()->set('LinkDefinition', 'text', [
			'url'   => 'http://example.com/',
			'title' => null
		]);

		$open = $this->inline->open('[]', 'text');

		$this->assertSame('http://example.com/', $open['attributes']['href']);
		$this->assertSame(strlen('[]'), $open['length']);
	}

	public function testOpenShortcutReference(): void
	{
		// no following bracket: the label itself is the reference id
		$this->parser->data()->set('LinkDefinition', 'text', [
			'url'   => 'http://example.com/',
			'title' => null
		]);

		$open = $this->inline->open('', 'text');

		$this->assertSame('http://example.com/', $open['attributes']['href']);
		$this->assertSame(0, $open['length']);
	}

	public function testOpenUnknownReference(): void
	{
		// a reference without a matching definition is not a link
		$this->assertNull($this->inline->open('[missing]', 'text'));
	}

	public function testOpenNoDestination(): void
	{
		// a plain label with nothing usable following it is not a link
		$this->assertNull($this->inline->open(' rest', 'text'));
	}

	public function testOpenEmptyDestination(): void
	{
		$open = $this->inline->open('()', 'text');

		$this->assertSame('', $open['attributes']['href']);
	}

	public function testOpenAngleDestination(): void
	{
		// spaces inside `<…>` are allowed and percent-encoded
		$open = $this->inline->open('(<http://example.com/x y>)', 'text');

		$this->assertSame('http://example.com/x%20y', $open['attributes']['href']);
	}

	public function testOpenBalancedParens(): void
	{
		$open = $this->inline->open('(/foo(bar))', 'text');

		$this->assertSame('/foo(bar)', $open['attributes']['href']);
	}

	public function testOpenUnbalancedParensIsNotLink(): void
	{
		// the unbalanced destination leaves no closing paren for the link
		$this->assertNull($this->inline->open('(/foo(bar)', 'text'));
	}

	public function testOpenParenthesizedTitle(): void
	{
		$open = $this->inline->open('(/uri (Title))', 'text');

		$this->assertSame('/uri', $open['attributes']['href']);
		$this->assertSame('Title', $open['attributes']['title']);
	}

	public function testOpenEntityDestination(): void
	{
		// entities are decoded, then the bytes percent-encoded
		$open = $this->inline->open('(/f&ouml;&ouml;)', 'text');

		$this->assertSame('/f%C3%B6%C3%B6', $open['attributes']['href']);
	}

	public function testParseInline(): void
	{
		$node = $this->link('[text](http://example.com/ "Title")');

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('a', $node->name);
		$this->assertSame('http://example.com/', $node->attributes['href']);
		$this->assertSame('Title', $node->attributes['title']);
		$this->assertInstanceOf(Text::class, $node->children[0]);
		$this->assertSame('text', $node->children[0]->text);
		$this->assertFalse($node->hasBreak());
	}

	public function testParseNoClosingBracket(): void
	{
		$this->assertNull($this->link('[no closing bracket'));
	}

	public function testParseNestedLinkDeclines(): void
	{
		// a link may not contain another link: the inner one wins and the
		// outer brackets stay literal, so `/v` never becomes a link
		$hrefs = [];

		foreach ($this->parser->inlines()->parse('[a [b](/u) c](/v)') as $node) {
			if ($node instanceof Element && $node->name === 'a') {
				$hrefs[] = $node->attributes['href'];
			}
		}

		$this->assertSame(['/u'], $hrefs);
	}

	public function testElementUnwrapsNestedAnchors(): void
	{
		// a link may not contain another anchor: the inner `<a>` in the
		// text is replaced by its text content when the element is built
		$children = [
			new Text('see '),
			new Element(name: 'a', attributes: ['href' => 'https://x'], children: [new Text('https://x')]),
			new Text(' now')
		];

		$element = $this->inline->element(['attributes' => []], $children);

		$this->assertCount(3, $element->children);
		$this->assertSame('https://x', $element->children[1]->text);
	}

	public function testElementUnwrapsNestedAnchorsDeeply(): void
	{
		// the flattening descends into nested elements
		$children = [
			new Element(name: 'em', children: [
				new Element(name: 'a', children: [new Text('u')])
			])
		];

		$element = $this->inline->element(['attributes' => []], $children);

		$this->assertSame('em', $element->children[0]->name);
		$this->assertSame('u', $element->children[0]->children[0]->text);
	}
}
