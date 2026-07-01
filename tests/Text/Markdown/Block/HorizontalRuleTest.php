<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HorizontalRule::class)]
class HorizontalRuleTest extends TestCase
{
	protected HorizontalRule $block;

	public function setUp(): void
	{
		$this->block = new HorizontalRule(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(HorizontalRule::class);
	}

	public function testConsumeAsterisks(): void
	{
		$line = new Line(['***']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('hr', $node->name);

		// the rule line is consumed
		$this->assertFalse($line->valid());
	}

	public function testConsumeSpacedHyphens(): void
	{
		// spaces between the markers are allowed
		$line = new Line(['- - -']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('hr', $node->name);
	}

	public function testConsumeUnderscores(): void
	{
		$line = new Line(['_____']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('hr', $node->name);
	}

	public function testConsumeTooFewMarkers(): void
	{
		// fewer than three markers is not a rule
		$line = new Line(['--']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testConsumeForeignCharacter(): void
	{
		// content other than the marker rules it out
		$line = new Line(['*a*']);
		$this->assertFalse($this->block->consume($line));
	}
}
