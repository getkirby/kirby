<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Delimiter;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Underscore::class)]
class UnderscoreTest extends TestCase
{
	protected Underscore $inline;

	public function setUp(): void
	{
		$this->inline = new Underscore(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Underscore::class);
	}

	public function testOpenClose(): void
	{
		// `_` may neither open nor close inside a word
		$this->assertSame([false, false], $this->inline->openClose('a', 'b'));
		$this->assertSame([true, false], $this->inline->openClose('', 'e'));

		// but it still opens/closes at a punctuation boundary
		$this->assertSame([true, false], $this->inline->openClose('(', 'e'));
	}

	public function testConsume(): void
	{
		$phrase = new Phrase('_em_');
		$node   = $this->inline->consume($phrase);

		$this->assertInstanceOf(Delimiter::class, $node);
		$this->assertSame('_', $node->marker);
		$this->assertSame(1, $node->length);
		$this->assertTrue($node->canOpen);
		$this->assertFalse($node->canClose);
		$this->assertSame('em_', $phrase->after());
	}

	public function testParse(): void
	{
		$parser = new Parser();

		$this->assertSame('<em>em</em>', $parser->parse('_em_', true));
		$this->assertSame('<strong>strong</strong>', $parser->parse('__strong__', true));

		// `_` may not emphasize inside a word
		$this->assertSame('snake_case und foo_bar', $parser->parse('snake_case und foo_bar', true));

		// but a punctuation boundary still emphasizes
		$this->assertSame('foo <em>(bar)</em>', $parser->parse('foo _(bar)_', true));
	}
}
