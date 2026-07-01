<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Comment::class)]
class CommentTest extends TestCase
{
	protected Comment $block;

	public function setUp(): void
	{
		$this->block = new Comment(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Comment::class);
	}

	public function testConsumeSingleLine(): void
	{
		$line = new Line(['<!-- a comment -->']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Html::class, $node);
		$this->assertSame('<!-- a comment -->', $node->html);
		$this->assertTrue($node->hasBreak());

		$this->assertFalse($line->valid());
	}

	public function testConsumeMultiLine(): void
	{
		$line = new Line(['<!-- start', 'middle', 'end -->', 'after']);
		$node = $this->block->consume($line);

		// every line up to the closing marker is collected
		$this->assertInstanceOf(Html::class, $node);
		$this->assertSame("<!-- start\nmiddle\nend -->", $node->html);

		// parsing continues after the comment
		$this->assertTrue($line->valid());
		$this->assertSame('after', $line->text());
	}

	public function testConsumeNoComment(): void
	{
		$line = new Line(['<span>not a comment</span>']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testConsumeNotInSafeMode(): void
	{
		$block = new Comment(new Parser(safe: true));
		$line  = new Line(['<!-- a comment -->']);

		$this->assertFalse($block->consume($line));
	}
}
