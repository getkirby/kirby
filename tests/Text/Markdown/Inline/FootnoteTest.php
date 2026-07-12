<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Footnote::class)]
class FootnoteTest extends TestCase
{
	protected Parser $parser;
	protected Footnote $inline;

	public function setUp(): void
	{
		$this->parser = new Parser();
		$this->inline   = new Footnote($this->parser);
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Footnote::class);
	}

	public function testConsume(): void
	{
		$this->parser->data()->set('Footnote', '1', ['count' => 0]);

		$phrase = new Phrase('[^1]');
		$node   = $this->inline->consume($phrase);

		// a superscript link to the footnote definition
		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('sup', $node->name);
		$this->assertSame(['id' => 'fnref1:1'], $node->attributes);
		$this->assertFalse($node->hasBreak());

		$anchor = $node->children[0];
		$this->assertSame('a', $anchor->name);
		$this->assertSame('#fn:1', $anchor->attributes['href']);
		$this->assertSame('footnote-ref', $anchor->attributes['class']);
		$this->assertSame('1', $anchor->children[0]->text);
	}

	public function testConsumeAssignsNumber(): void
	{
		// numbering follows the order of first reference
		$this->parser->data()->set('Footnote', 'a', ['count' => 0]);
		$this->parser->data()->set('Footnote', 'b', ['count' => 0]);

		$this->inline->consume(new Phrase('[^a]'));
		$node = $this->inline->consume(new Phrase('[^b]'));

		$this->assertSame('2', $node->children[0]->children[0]->text);
	}

	public function testConsumeNotAFootnote(): void
	{
		// a plain bracket without a caret is not a footnote reference
		$phrase = new Phrase('[link]');

		$this->assertFalse($this->inline->consume($phrase));
	}

	public function testConsumeEmptyName(): void
	{
		// a caret bracket with no name does not match the pattern
		$phrase = new Phrase('[^]');

		$this->assertFalse($this->inline->consume($phrase));
	}

	public function testConsumeUndefined(): void
	{
		// a reference without a matching definition is not consumed
		$phrase = new Phrase('[^unknown]');

		$this->assertFalse($this->inline->consume($phrase));
	}
}
