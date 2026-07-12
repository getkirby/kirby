<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
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

	public function testConsumeTrailingContent(): void
	{
		// a block-level tag (type 6) keeps any trailing text on its line
		$line = new Line(['<hr> trailing text']);
		$node = $this->block->consume($line);

		$this->assertSame('<hr> trailing text', $node->html);
	}

	public function testConsumeEndsAtBlankLine(): void
	{
		// a type 6 block ends at a blank line, not at a closing tag, so
		// the closing tag and following text stay in the same block
		$line = new Line(['<div>content</div>', 'after', '', 'para']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(HtmlNode::class, $node);
		$this->assertSame("<div>content</div>\nafter", $node->html);
		$this->assertTrue($line->isBlank());
	}

	public function testConsumeBlankLineEndsBlock(): void
	{
		// a blank line ends a type 6 block and is left unconsumed
		$line = new Line(['<div>', 'content', '', 'more', '</div>']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(HtmlNode::class, $node);
		$this->assertSame("<div>\ncontent", $node->html);
		$this->assertTrue($line->isBlank());
	}

	public function testConsumeNestedElement(): void
	{
		// without a blank line the block runs to the end of input,
		// keeping any nested tags verbatim
		$line = new Line(['<div>', '<div>', 'nested', '</div>', '</div>']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(HtmlNode::class, $node);
		$this->assertSame("<div>\n<div>\nnested\n</div>\n</div>", $node->html);
	}

	public function testConsumeRawText(): void
	{
		// type 1 (script/pre/style/textarea) ends at its closing tag,
		// even across blank lines; the closing line is included
		$line = new Line(['<pre>', 'a', '', 'b', '</pre>', 'after']);
		$node = $this->block->consume($line);

		$this->assertSame("<pre>\na\n\nb\n</pre>", $node->html);
		$this->assertSame('after', $line->text());
	}

	public function testConsumeDeclaration(): void
	{
		// type 4 (a declaration like <!DOCTYPE>) ends at the first `>`
		$line = new Line(['<!DOCTYPE html>']);
		$node = $this->block->consume($line);

		$this->assertSame('<!DOCTYPE html>', $node->html);
	}

	public function testConsumeCompleteTag(): void
	{
		// type 7: a complete tag (even an inline one) alone on the line
		// opens a block that ends at the next blank line
		$line = new Line(['<a href="foo">', '*bar*', '</a>']);
		$node = $this->block->consume($line);

		$this->assertSame("<a href=\"foo\">\n*bar*\n</a>", $node->html);
	}

	public function testConsumeCompleteTagCannotInterruptParagraph(): void
	{
		// type 7 may not interrupt a paragraph that is still continuing
		$line = new Line(['<a href="foo">']);
		$paragraph = new Element(name: 'p', content: 'text');

		$this->assertFalse($this->block->consume($line, $paragraph));
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

	public function testConsumeReparsesSingleLineElement(): void
	{
		// a `markdown="1"` element closing on its opening line
		$line = new Line(['<div markdown="1">*x*</div>']);
		$node = $this->block->consume($line);

		$this->assertStringContainsString('<em>x</em>', $node->html);
		$this->assertStringNotContainsString('markdown=', $node->html);
	}

	public function testConsumeReparsesWithBlankLines(): void
	{
		// interior blank lines are captured and preserved for the re-parse
		$line = new Line(['<div markdown="1">', '', '*a*', '', '*b*', '</div>']);
		$node = $this->block->consume($line);

		$this->assertStringContainsString('<em>a</em>', $node->html);
		$this->assertStringContainsString('<em>b</em>', $node->html);
	}

	public function testConsumeCapturesNestedSameTag(): void
	{
		// a nested element of the same tag increments the depth, so the
		// first `</div>` does not end the outer element early — the whole
		// element (both divs) is captured and its `markdown=` removed
		$line = new Line([
			'<div markdown="1">',
			'<div>',
			'inner',
			'</div>',
			'outer',
			'</div>'
		]);
		$node = $this->block->consume($line);

		$this->assertSame(2, substr_count($node->html, '<div>'));
		$this->assertStringContainsString('outer', $node->html);
		$this->assertStringNotContainsString('markdown=', $node->html);
	}
}
