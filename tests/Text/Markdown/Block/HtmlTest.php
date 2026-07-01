<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Html as HtmlNode;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Html::class)]
class HtmlTest extends TestCase
{
	protected Html $block;

	public function setUp(): void
	{
		$this->block = new Html(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Html::class);
	}

	public function testConsume(): void
	{
		$line = new Line(['<div>', 'content', '</div>']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(HtmlNode::class, $node);
		$this->assertSame("<div>\ncontent\n</div>", $node->html);
		$this->assertTrue($node->hasBreak());

		$this->assertFalse($line->valid());
	}

	public function testConsumeSafeMode(): void
	{
		$block = new Html(new Parser(safe: true));
		$line  = new Line(['<div>', 'content', '</div>']);
		$this->assertFalse($block->consume($line));
	}

	public function testConsumeTextLevelTag(): void
	{
		// a text-level (inline) tag is left for the span parser
		$line = new Line(['<em>emphasis</em>']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testConsumeNotHtml(): void
	{
		$line = new Line(['< not a tag']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testConsumeVoidElement(): void
	{
		// a void tag on its own line closes the block immediately
		$line = new Line(['<hr>']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(HtmlNode::class, $node);
		$this->assertSame('<hr>', $node->html);
		$this->assertFalse($line->valid());
	}

	public function testConsumeSelfClosingElement(): void
	{
		// a self-closing tag on its own line closes the block immediately
		$line = new Line(['<div/>']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(HtmlNode::class, $node);
		$this->assertSame('<div/>', $node->html);
		$this->assertFalse($line->valid());
	}

	public function testConsumeVoidElementWithContent(): void
	{
		// a void or self-closing tag followed by content is not a block
		$line = new Line(['<hr> trailing text']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testConsumeClosedInline(): void
	{
		// a tag that opens and closes on the same line ends the block there
		$line = new Line(['<div>content</div>', 'after']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(HtmlNode::class, $node);
		$this->assertSame('<div>content</div>', $node->html);
		$this->assertTrue($line->valid());
	}

	public function testConsumeBlankLine(): void
	{
		// a blank line within the block is preserved as an empty line
		$line = new Line(['<div>', 'content', '', 'more', '</div>']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(HtmlNode::class, $node);
		$this->assertSame("<div>\ncontent\n\nmore\n</div>", $node->html);
	}

	public function testConsumeNestedElement(): void
	{
		// a nested tag of the same name is tracked by depth
		// so the block  closes only at the matching outer
		// closing tag
		$line = new Line(['<div>', '<div>', 'nested', '</div>', '</div>']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(HtmlNode::class, $node);
		$this->assertSame("<div>\n<div>\nnested\n</div>\n</div>", $node->html);
	}

	public function testConsumeReparsesMarkdown(): void
	{
		// content of an element marked markdown="1" is re-parsed
		$line = new Line(['<div markdown="1">', '*emphasis*', '</div>']);
		$node = $this->block->consume($line);

		$this->assertStringContainsString('<em>emphasis</em>', $node->html);
		$this->assertStringNotContainsString('markdown=', $node->html);
	}

	public function testConsumeReparsesNestedMarkdown(): void
	{
		// markdown="1" nested inside a plain block is still re-parsed,
		// while the surrounding markup is left untouched
		$line = new Line(['<div>', '<p markdown="1">*text*</p>', '</div>']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(HtmlNode::class, $node);
		$this->assertStringContainsString('<em>text</em>', $node->html);
		$this->assertStringContainsString('<div>', $node->html);
		$this->assertStringNotContainsString('markdown=', $node->html);
	}

}
