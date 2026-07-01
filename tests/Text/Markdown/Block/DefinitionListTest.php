<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DefinitionList::class)]
class DefinitionListTest extends TestCase
{
	protected Parser $parser;
	protected DefinitionList $block;

	public function setUp(): void
	{
		$this->parser = new Parser();
		$this->block  = new DefinitionList($this->parser);
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(DefinitionList::class);
	}

	public function testConsume(): void
	{
		$line = new Line(['Orange', ':   The fruit of an evergreen tree.']);
		$line->next();
		$paragraph = new Element(name: 'p', content: 'Orange');

		$node = $this->block->consume($line, $paragraph);

		// the open paragraph becomes the list in place
		$this->assertSame($paragraph, $node);
		$this->assertSame('dl', $node->name);

		$this->assertSame('dt', $node->children[0]->name);
		$this->assertSame('Orange', $node->children[0]->content);

		$this->assertSame('dd', $node->children[1]->name);
		$this->assertSame('The fruit of an evergreen tree.', $node->children[1]->content);
	}

	public function testConsumeWithoutParagraph(): void
	{
		$line = new Line([':   A definition without a term.']);
		$this->assertFalse($this->block->consume($line, null));
	}

	public function testConsumeMultipleDefinitions(): void
	{
		$line = new Line(['Orange', ':   Fruit one.', ':   Fruit two.']);
		$line->next();
		$paragraph = new Element(name: 'p', content: 'Orange');

		$node = $this->block->consume($line, $paragraph);

		// each `:` line opens a new definition
		$this->assertCount(3, $node->children);
		$this->assertSame('dd', $node->children[1]->name);
		$this->assertSame('Fruit one.', $node->children[1]->content);
		$this->assertSame('dd', $node->children[2]->name);
		$this->assertSame('Fruit two.', $node->children[2]->content);
	}

	public function testConsumeEndsAtUnindentedLine(): void
	{
		$line = new Line(['Orange', ':   Fruit.', '', 'Next paragraph']);
		$line->next();
		$paragraph = new Element(name: 'p', content: 'Orange');

		$node = $this->block->consume($line, $paragraph);

		// a blank line followed by an unindented line ends the list
		$this->assertCount(2, $node->children);
		$this->assertSame('dd', $node->children[1]->name);
		$this->assertSame('Fruit.', $node->children[1]->content);
	}

	public function testConsumeLooseDefinition(): void
	{
		$line = new Line(['Orange', ':   Fruit.', '', '    More text.']);
		$line->next();
		$paragraph = new Element(name: 'p', content: 'Orange');

		$node = $this->block->consume($line, $paragraph);

		// a blank line turns the current definition block-level (loose)
		$this->assertSame('dd', $node->children[1]->name);
		$this->assertTrue($node->children[1]->block);
		$this->assertSame("Fruit.\n\n\nMore text.", $node->children[1]->content);
	}

	public function testConsumeLooseNewDefinition(): void
	{
		$line = new Line(['Orange', ':   Fruit.', '', ':   Another definition.']);
		$line->next();
		$paragraph = new Element(name: 'p', content: 'Orange');

		$node = $this->block->consume($line, $paragraph);

		// after a blank line, a new `:` definition becomes block-level
		$this->assertCount(3, $node->children);
		$this->assertSame('Another definition.', $node->children[2]->content);
		$this->assertTrue($node->children[2]->block);
	}

	public function testTransformMergesAdjacentLists(): void
	{
		$this->parser->data()->set('DefinitionList', 1, true);
		$this->parser->data()->set('DefinitionList', 2, true);

		$a = new Element(name: 'dl', children: [new Element(name: 'dt', content: 'A')]);
		$b = new Element(name: 'dl', children: [new Element(name: 'dt', content: 'B')]);

		$result = $this->block->transform([$a, $b]);

		// the two sibling lists are folded into one
		$this->assertCount(1, $result);
		$this->assertCount(2, $result[0]->children);
	}

	public function testTransformSkipsWhenSingleList(): void
	{
		// nothing to merge with fewer than two lists
		$this->parser->data()->set('DefinitionList', 1, true);

		$a     = new Element(name: 'dl', children: []);
		$nodes = [$a];

		$this->assertSame($nodes, $this->block->transform($nodes));
	}
}
