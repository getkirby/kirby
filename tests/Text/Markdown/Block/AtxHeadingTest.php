<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AtxHeading::class)]
class AtxHeadingTest extends TestCase
{
	protected AtxHeading $block;

	public function setUp(): void
	{
		$this->block = new AtxHeading(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(AtxHeading::class);
	}

	public function testConsume(): void
	{
		$line = new Line(['# Heading']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('h1', $node->name);
		$this->assertSame('Heading', $node->content);
		$this->assertTrue($node->multiline);

		// the heading line is consumed
		$this->assertFalse($line->valid());
	}

	public function testConsumeLevel(): void
	{
		$line = new Line(['### Heading']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('h3', $node->name);
	}

	public function testConsumeClosingHashes(): void
	{
		// the optional cosmetic closing hashes are trimmed
		$line = new Line(['## Heading ##']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('h2', $node->name);
		$this->assertSame('Heading', $node->content);
	}

	public function testConsumeTooManyHashes(): void
	{
		$line = new Line(['####### Heading']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testConsumeRequiresSpaceAfterHashes(): void
	{
		// the opening #s must be followed by a space (or end of line)
		$this->assertFalse($this->block->consume(new Line(['#5 bolt'])));
		$this->assertFalse($this->block->consume(new Line(['#hashtag'])));
	}

	public function testConsumeClosingHashesNeedSpace(): void
	{
		// a trailing # not preceded by whitespace is part of the content
		$line = new Line(['# foo#']);
		$node = $this->block->consume($line);

		$this->assertSame('foo#', $node->content);
	}

	public function testConsumeAttributes(): void
	{
		// a trailing block sets id and class and is stripped
		$line = new Line(['### Heading {#id .cls}']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('h3', $node->name);
		$this->assertSame('Heading', $node->content);
		$this->assertSame(['id' => 'id', 'class' => 'cls'], $node->attributes);
	}
}
