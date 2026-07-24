<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Footnotes::class)]
class FootnotesTest extends TestCase
{
	protected Parser $parser;
	protected Footnotes $block;

	public function setUp(): void
	{
		$this->parser = new Parser();
		$this->block  = new Footnotes($this->parser);
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Footnotes::class);
	}

	public function testConsume(): void
	{
		$line = new Line(["[^1]: And that's the footnote."]);

		// the definition is stored and produces no output
		$this->assertNull($this->block->consume($line));
		$this->assertSame([
			'text'   => "And that's the footnote.",
			'count'  => 0,
			'number' => null
		], $this->parser->data()->get('Footnote', '1'));
		$this->assertFalse($line->valid());
	}

	public function testConsumeNoDefinition(): void
	{
		// a plain link shares the `[` marker
		// but is not a footnote
		$line = new Line(['[link](http://example.com)']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testConsumeInvalidDefinition(): void
	{
		// starts with `[^` but is missing the closing `]:` colon
		$line = new Line(['[^1] not a definition']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testConsumeMultiline(): void
	{
		$line = new Line([
			'[^1]: First line',
			'Second line'
		]);

		// consecutive lines are joined with a single newline
		$this->assertNull($this->block->consume($line));
		$this->assertSame([
			'text'   => "First line\nSecond line",
			'count'  => 0,
			'number' => null
		], $this->parser->data()->get('Footnote', '1'));

		$this->assertFalse($line->valid());
	}

	public function testConsumeIndentedContinuation(): void
	{
		$line = new Line([
			'[^1]: First paragraph',
			'',
			'    Second paragraph'
		]);

		// after a blank line only indented lines continue the footnote,
		// joined as a new paragraph
		$this->assertNull($this->block->consume($line));
		$this->assertSame([
			'text'   => "First paragraph\n\nSecond paragraph",
			'count'  => 0,
			'number' => null
		], $this->parser->data()->get('Footnote', '1'));

		$this->assertFalse($line->valid());
	}

	public function testConsumeInterruptedByUnindentedLine(): void
	{
		$line = new Line([
			'[^1]: The footnote.',
			'',
			'Not indented, ends the footnote.'
		]);

		// a blank line followed by an unindented line closes the footnote
		$this->assertNull($this->block->consume($line));
		$this->assertSame([
			'text'   => 'The footnote.',
			'count'  => 0,
			'number' => null
		], $this->parser->data()->get('Footnote', '1'));

		$this->assertTrue($line->valid());
	}

	public function testConsumeClosedByNextDefinition(): void
	{
		$line = new Line([
			'[^1]: The first footnote.',
			'[^2]: The second footnote.'
		]);

		// a new footnote definition closes the current one
		$this->assertNull($this->block->consume($line));
		$this->assertSame([
			'text'   => 'The first footnote.',
			'count'  => 0,
			'number' => null
		], $this->parser->data()->get('Footnote', '1'));

		$this->assertTrue($line->valid());
	}

	public function testTransformAppendsSection(): void
	{
		$this->parser->data()->set('Footnote', '1', [
			'text'   => 'The footnote.',
			'count'  => 1,
			'number' => 1
		]);

		$nodes  = [new Element(name: 'p', content: 'text')];
		$result = $this->block->transform($nodes);

		// the footnotes section is appended to the document
		$this->assertCount(2, $result);
		$this->assertInstanceOf(Element::class, $result[1]);
		$this->assertSame('div', $result[1]->name);
		$this->assertSame(['class' => 'footnotes'], $result[1]->attributes);
	}

	public function testTransformAppendsBacklinksParagraph(): void
	{
		// content that does not end in a paragraph gets
		// the back-reference links appended as a new paragraph;
		// multiple references are separated
		$this->parser->data()->set('Footnote', '1', [
			'text'   => '# Heading',
			'count'  => 2,
			'number' => 1
		]);

		$result = $this->block->transform([]);

		$this->assertCount(1, $result);
		$this->assertInstanceOf(Element::class, $result[0]);
		$this->assertSame('div', $result[0]->name);
	}

	public function testTransformWithoutFootnotes(): void
	{
		// nothing is appended when no footnotes were defined
		$nodes = [new Element(name: 'p', content: 'text')];
		$this->assertSame($nodes, $this->block->transform($nodes));
	}
}
