<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Lists::class)]
class ListsTest extends TestCase
{
	protected Lists $block;

	public function setUp(): void
	{
		$this->block = new Lists(new Parser());
	}

	/**
	 * The resolved text of a list item's first block (its content is
	 * parsed into block nodes during `consume()`).
	 */
	protected function content(Element $item): string|null
	{
		return $item->children[0]->content ?? null;
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Lists::class);
	}

	public function testConsumeUnordered(): void
	{
		$line = new Line(['-   Red', '-   Green', '-   Blue']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('ul', $node->name);
		$this->assertCount(3, $node->children);

		$this->assertSame('li', $node->children[0]->name);
		$this->assertSame('Red', $this->content($node->children[0]));
		$this->assertSame('Green', $this->content($node->children[1]));
	}

	public function testConsumeUnorderedAsterisk(): void
	{
		$line = new Line(['*   Red', '*   Green']);
		$node = $this->block->consume($line);

		$this->assertSame('ul', $node->name);
		$this->assertSame('Red', $this->content($node->children[0]));
		$this->assertSame('Green', $this->content($node->children[1]));
	}

	public function testConsumeUnorderedPlus(): void
	{
		$line = new Line(['+   Red', '+   Green']);
		$node = $this->block->consume($line);

		$this->assertSame('ul', $node->name);
		$this->assertSame('Red', $this->content($node->children[0]));
		$this->assertSame('Green', $this->content($node->children[1]));
	}

	public function testConsumeOrdered(): void
	{
		$line = new Line(['1.  Bird', '2.  McHale', '3.  Parish']);
		$node = $this->block->consume($line);

		$this->assertSame('ol', $node->name);
		$this->assertCount(3, $node->children);

		// a list starting at 1 needs no start attribute
		$this->assertSame([], $node->attributes);
		$this->assertSame('Bird', $this->content($node->children[0]));
	}

	public function testConsumeOrderedWithStart(): void
	{
		// a list not starting at 1 records the start attribute
		$line = new Line(['3.  Third', '4.  Fourth']);
		$node = $this->block->consume($line);

		$this->assertSame('ol', $node->name);
		$this->assertSame(['start' => '3'], $node->attributes);
	}

	public function testConsumeDeeplyIndentedMarker(): void
	{
		// five or more spaces after the marker keep the surplus
		// indentation, so the item content becomes an indented code block
		$line = new Line(['-     Red']);
		$node = $this->block->consume($line);

		$this->assertSame('ul', $node->name);
		$this->assertSame('pre', $node->children[0]->children[0]->name);
	}

	public function testConsumeBareMarker(): void
	{
		// a marker without any content produces an empty item
		$line = new Line(['-']);
		$node = $this->block->consume($line);

		$this->assertSame('ul', $node->name);
		$this->assertCount(1, $node->children);
		$this->assertSame([], $node->children[0]->children);
	}

	public function testConsumeOrderedWithStartInterruptsParagraph(): void
	{
		// a non-1 start must not interrupt an open paragraph
		$paragraph = new Element(name: 'p');
		$line      = new Line(['3.  Third']);

		$this->assertFalse($this->block->consume($line, $paragraph));
	}

	public function testConsumeEmptyItemDoesNotInterruptParagraph(): void
	{
		// an item whose first line carries no content cannot interrupt
		// an open paragraph
		$paragraph = new Element(name: 'p');
		$line      = new Line(['*']);

		$this->assertFalse($this->block->consume($line, $paragraph));
	}

	public function testConsumeMarkerIndentedMoreThanThreeSpaces(): void
	{
		// as items indent progressively, a marker more than three spaces
		// past the list's own indentation no longer opens an item
		$line = new Line(['- a', ' - b', '  - c', '   - d', '    - e']);
		$node = $this->block->consume($line);

		$this->assertCount(4, $node->children);
		$this->assertSame('a', $this->content($node->children[0]));
		$this->assertSame('d', $this->content($node->children[3]));
	}

	public function testConsumeInteriorBlankLinesArePreserved(): void
	{
		// consecutive blank lines inside an item are kept verbatim, so an
		// indented code block keeps its interior blank lines
		$line = new Line(['-   Foo', '', '        bar', '', '', '        baz']);
		$node = $this->block->consume($line);

		// li -> pre -> code -> text
		$code = $node->children[0]->children[1]->children[0]->children[0];
		$this->assertSame("bar\n\n\nbaz\n", $code->text);
	}

	public function testConsumeLooseList(): void
	{
		// a blank line between items turns the list loose:
		// items keep their paragraph wrappers
		$line = new Line(['-   Red', '', '-   Green']);
		$node = $this->block->consume($line);

		$this->assertSame('ul', $node->name);
		$this->assertCount(2, $node->children);
		$this->assertSame('p', $node->children[0]->children[0]->name);
		$this->assertSame('Red', $this->content($node->children[0]));
		$this->assertSame('Green', $this->content($node->children[1]));
	}

	public function testConsumeEmptyItemFollowedByBlank(): void
	{
		// a blank line right after an item that never
		// got content closes the list
		$line = new Line(['-', '', 'text']);
		$node = $this->block->consume($line);

		$this->assertSame('ul', $node->name);
		$this->assertCount(1, $node->children);
		$this->assertSame([], $node->children[0]->children);
	}

	public function testConsumeDifferentListEndsList(): void
	{
		// a different list marker starts a new list and ends this one
		$line = new Line(['-   Red', '*   Star']);
		$node = $this->block->consume($line);

		$this->assertSame('ul', $node->name);
		$this->assertCount(1, $node->children);
		$this->assertSame('Red', $this->content($node->children[0]));
		$this->assertTrue($line->matches('/^\*/'));
	}

	public function testConsumeReferenceInsideList(): void
	{
		// a reference definition inside the list is registered
		// but produces no item and consumes its own line
		$line = new Line(['-   Red', '[id]: http://example.com']);
		$node = $this->block->consume($line);

		$this->assertSame('ul', $node->name);
		$this->assertCount(1, $node->children);
		$this->assertSame('Red', $this->content($node->children[0]));
		$this->assertFalse($line->valid());
	}

	public function testConsumeIndentedContinuationAfterBlank(): void
	{
		// indented content after a blank line is appended to the current
		// item; the interior blank makes the item (and list) loose
		$line = new Line(['-   Red', '', '    more']);
		$node = $this->block->consume($line);

		$this->assertSame('ul', $node->name);
		$this->assertCount(1, $node->children);
		$this->assertCount(2, $node->children[0]->children);
		$this->assertSame('p', $node->children[0]->children[0]->name);
		$this->assertSame('Red', $this->content($node->children[0]));
	}

	public function testConsumeLazyContinuation(): void
	{
		// a non-indented line without a preceding blank line
		// lazily continues the current item
		$line = new Line(['-   Red', 'lazy text']);
		$node = $this->block->consume($line);

		$this->assertSame('ul', $node->name);
		$this->assertCount(1, $node->children);
		$this->assertSame("Red\nlazy text", $this->content($node->children[0]));
	}

	public function testConsumeParagraphAfterBlankEndsList(): void
	{
		// a non-indented paragraph after a blank line ends the list; the
		// blank is handed back to the enclosing block, so the cursor rests
		// on it (not yet on the paragraph)
		$line = new Line(['-   Red', '', 'after blank']);
		$node = $this->block->consume($line);

		$this->assertSame('ul', $node->name);
		$this->assertCount(1, $node->children);
		$this->assertSame('Red', $this->content($node->children[0]));
		$this->assertTrue($line->isBlank());
	}

	public function testConsumeNoList(): void
	{
		$line = new Line(['just some text']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testConsumeThematicBreakEndsList(): void
	{
		// `- - -` matches the item marker but is a thematic break, which
		// takes precedence and ends the list
		$line = new Line(['- a', '- - -']);
		$node = $this->block->consume($line);

		// the list holds only the first item; the break is left unconsumed
		$this->assertSame('ul', $node->name);
		$this->assertCount(1, $node->children);
		$this->assertTrue($line->valid());
	}
}
