<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SetextHeading::class)]
class SetextHeadingTest extends TestCase
{
	protected SetextHeading $block;

	public function setUp(): void
	{
		$this->block = new SetextHeading(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(SetextHeading::class);
	}

	public function testConsumeH1(): void
	{
		$paragraph = new Element(name: 'p', content: 'Title');
		$line      = new Line(['Title', '====']);
		$line->next();

		$node = $this->block->consume($line, $paragraph);

		// the paragraph is promoted to a heading in place
		$this->assertSame($paragraph, $node);
		$this->assertSame('h1', $node->name);
		$this->assertSame('Title', $node->content);
		$this->assertFalse($line->valid());
	}

	public function testConsumeH2(): void
	{
		$paragraph = new Element(name: 'p', content: 'Title');
		$line      = new Line(['Title', '----']);
		$line->next();

		$node = $this->block->consume($line, $paragraph);

		$this->assertSame('h2', $node->name);
	}

	public function testConsumeAttributesOnTextLine(): void
	{
		$paragraph = new Element(name: 'p', content: 'Title {#id .cls}');
		$line      = new Line(['====']);

		$node = $this->block->consume($line, $paragraph);

		$this->assertSame('h1', $node->name);
		$this->assertSame('Title', $node->content);
		$this->assertSame(['id' => 'id', 'class' => 'cls'], $node->attributes);
	}

	public function testConsumeWithoutParagraph(): void
	{
		// a setext underline needs an open paragraph to promote
		$line = new Line(['====']);
		$this->assertFalse($this->block->consume($line, null));
	}

	public function testConsumeBlankBeforeUnderline(): void
	{
		// a blank line breaks the paragraph,
		// so the underline is not one
		$paragraph = new Element(name: 'p', content: 'Title');
		$line      = new Line(['', '====']);
		$line->next();

		$this->assertFalse($this->block->consume($line, $paragraph));
	}

	public function testConsumeNotAnUnderline(): void
	{
		// a line mixing the marker with other content
		// is not an underline
		$paragraph = new Element(name: 'p', content: 'Title');
		$line      = new Line(['Title', '=== text']);
		$line->next();

		$this->assertFalse($this->block->consume($line, $paragraph));
	}
}
