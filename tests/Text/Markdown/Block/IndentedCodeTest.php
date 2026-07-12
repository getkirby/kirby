<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IndentedCode::class)]
class IndentedCodeTest extends TestCase
{
	protected IndentedCode $block;

	public function setUp(): void
	{
		$this->block = new IndentedCode(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(IndentedCode::class);
	}

	public function testConsume(): void
	{
		$line = new Line(['    $foo = 1;']);
		$node = $this->block->consume($line);

		// wrapped in <pre><code> with the four-space indent removed
		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('pre', $node->name);

		$code = $node->children[0];
		$this->assertSame('code', $code->name);
		$this->assertInstanceOf(Text::class, $code->children[0]);
		// the content keeps its terminating newline (CommonMark)
		$this->assertSame("\$foo = 1;\n", $code->children[0]->text);
	}

	public function testConsumeMultiLine(): void
	{
		$line = new Line(['    a', '    b']);
		$node = $this->block->consume($line);
		$this->assertSame("a\nb\n", $node->children[0]->children[0]->text);
	}

	public function testConsumeBlankLineBetween(): void
	{
		// a blank line inside the block is preserved
		$line = new Line(['    a', '', '    b']);
		$node = $this->block->consume($line);
		$this->assertSame("a\n\nb\n", $node->children[0]->children[0]->text);
	}

	public function testConsumeDedentEnds(): void
	{
		// a non-indented line ends the block and
		// is left for the next parser
		$line = new Line(['    a', 'b']);
		$node = $this->block->consume($line);
		$this->assertSame("a\n", $node->children[0]->children[0]->text);
		$this->assertSame('b', $line->text());
	}

	public function testConsumeDoesNotInterruptParagraph(): void
	{
		// an indent right after a text line
		// is a lazy paragraph continuation
		$line = new Line(['text', '    code']);
		$line->next();
		$paragraph = new Element(name: 'p', content: 'text');

		$this->assertFalse($this->block->consume($line, $paragraph));
	}
}
