<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Table::class)]
class TableTest extends TestCase
{
	protected Table $block;

	public function setUp(): void
	{
		$this->block = new Table(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Table::class);
	}

	public function testConsume(): void
	{
		$paragraph = new Element(name: 'p', content: 'First Header | Second Header');
		$line      = new Line([
			'First Header | Second Header',
			'------------ | ------------',
			'Content Cell | Another Cell'
		]);
		$line->next();

		$node = $this->block->consume($line, $paragraph);

		// the header paragraph becomes the table in place
		$this->assertSame($paragraph, $node);
		$this->assertSame('table', $node->name);

		$thead = $node->children[0];
		$this->assertSame('thead', $thead->name);
		$headerCells = $thead->children[0]->children;
		$this->assertSame('th', $headerCells[0]->name);
		$this->assertSame('First Header', $headerCells[0]->content);
		$this->assertSame('Second Header', $headerCells[1]->content);

		$tbody = $node->children[1];
		$this->assertSame('tbody', $tbody->name);
		$bodyCells = $tbody->children[0]->children;
		$this->assertSame('td', $bodyCells[0]->name);
		$this->assertSame('Content Cell', $bodyCells[0]->content);
		$this->assertSame('Another Cell', $bodyCells[1]->content);
	}

	public function testConsumeAlignments(): void
	{
		$paragraph = new Element(name: 'p', content: 'a | b | c');
		$line      = new Line([
			'a | b | c',
			':--- | :---: | ---:'
		]);
		$line->next();

		$node  = $this->block->consume($line, $paragraph);
		$cells = $node->children[0]->children[0]->children;

		$this->assertSame(['style' => 'text-align: left;'], $cells[0]->attributes);
		$this->assertSame(['style' => 'text-align: center;'], $cells[1]->attributes);
		$this->assertSame(['style' => 'text-align: right;'], $cells[2]->attributes);
	}

	public function testConsumeLeadingPipe(): void
	{
		$paragraph = new Element(name: 'p', content: 'a | b');
		$line      = new Line([
			'a | b',
			'| --- | --- |'
		]);
		$line->next();

		$node = $this->block->consume($line, $paragraph);
		$this->assertSame('table', $node->name);
	}

	public function testConsumeWithoutParagraph(): void
	{
		$line = new Line(['--- | ---']);
		$this->assertFalse($this->block->consume($line, null));
	}

	public function testConsumeMultiLineParagraph(): void
	{
		// the header must be a single line
		$paragraph = new Element(name: 'p', content: "First\nSecond");
		$line      = new Line(['--- | ---']);
		$this->assertFalse($this->block->consume($line, $paragraph));
	}

	public function testConsumeInvalidDelimiter(): void
	{
		// the delimiter row may only contain -, :, | and spaces
		$paragraph = new Element(name: 'p', content: 'a | b');
		$line      = new Line(['not a delimiter']);
		$this->assertFalse($this->block->consume($line, $paragraph));
	}

	public function testConsumeWithoutColumns(): void
	{
		// neither the paragraph nor the delimiter row
		// contain a pipe or colon, so there are no columns
		$paragraph = new Element(name: 'p', content: 'Header');
		$line      = new Line(['---']);
		$this->assertFalse($this->block->consume($line, $paragraph));
	}

	public function testConsumeEmptyDelimiterCell(): void
	{
		// an empty cell in the delimiter row is invalid
		$paragraph = new Element(name: 'p', content: 'a | b | c');
		$line      = new Line(['--- | | ---']);
		$this->assertFalse($this->block->consume($line, $paragraph));
	}

	public function testConsumeColumnCountMismatch(): void
	{
		// the header has fewer columns than the delimiter row
		$paragraph = new Element(name: 'p', content: 'a | b');
		$line      = new Line(['--- | --- | ---']);
		$this->assertFalse($this->block->consume($line, $paragraph));
	}

	public function testConsumeStopsAtBlankLine(): void
	{
		$paragraph = new Element(name: 'p', content: 'a | b');
		$line      = new Line([
			'a | b',
			'--- | ---',
			'Content Cell | Another Cell',
			'',
			'Ignored | Cell'
		]);
		$line->next();

		$node  = $this->block->consume($line, $paragraph);
		$tbody = $node->children[1];

		// only the row before the blank line becomes part of the table
		$this->assertCount(1, $tbody->children);
	}

	public function testConsumeStopsAtNonRow(): void
	{
		$paragraph = new Element(name: 'p', content: 'a | b');
		$line      = new Line([
			'a | b',
			'--- | ---',
			'Content Cell | Another Cell',
			'not a row'
		]);
		$line->next();

		$node  = $this->block->consume($line, $paragraph);
		$tbody = $node->children[1];

		// the trailing line without a pipe is not part of the table
		$this->assertCount(1, $tbody->children);
	}
}
