<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Delimiter;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Strikethrough::class)]
class StrikethroughTest extends TestCase
{
	protected Strikethrough $inline;

	public function setUp(): void
	{
		$this->inline = new Strikethrough(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Strikethrough::class);
	}

	public function testPair(): void
	{
		// a single tilde is too short to pair
		$this->assertNull($this->inline->pair(1));
		$this->assertSame([2, 'del'], $this->inline->pair(2));
	}

	public function testConsume(): void
	{
		$phrase = new Phrase('~~struck~~');
		$node   = $this->inline->consume($phrase);

		$this->assertInstanceOf(Delimiter::class, $node);
		$this->assertSame('~', $node->marker);
		$this->assertSame(2, $node->length);
		$this->assertTrue($node->canOpen);
		$this->assertSame('struck~~', $phrase->after());
	}

	public function testParse(): void
	{
		$parser = new Parser();

		$this->assertSame('<del>struck</del>', $parser->parse('~~struck~~', true));

		// a single tilde is not a strikethrough
		$this->assertSame('~x~', $parser->parse('~x~', true));

		// content flanked by whitespace does not pair
		$this->assertSame('~~ x ~~', $parser->parse('~~ x ~~', true));
	}
}
