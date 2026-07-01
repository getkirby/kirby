<?php

namespace Kirby\Text\Markdown;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Parser::class)]
class ParserTest extends TestCase
{
	public function testConstruct(): void
	{
		$parser = new Parser(breaks: true, safe: true);

		$this->assertTrue($parser->breaks);
		$this->assertTrue($parser->safe);
	}

	public function testConstructDefaults(): void
	{
		$parser = new Parser();

		$this->assertFalse($parser->breaks);
		$this->assertFalse($parser->safe);
	}

	public function testParseNull(): void
	{
		$this->assertSame('', (new Parser())->parse(null));
	}

	public function testParseEmpty(): void
	{
		$this->assertSame('', (new Parser())->parse(''));
	}

	public function testParseBlock(): void
	{
		$this->assertSame('<h1>Title</h1>', (new Parser())->parse('# Title'));
		$this->assertSame('<p>hello</p>', (new Parser())->parse('hello'));
	}

	public function testParseInline(): void
	{
		// inline mode skips the block layer (no wrapping <p>)
		$this->assertSame('<em>em</em>', (new Parser())->parse('*em*', inline: true));
		$this->assertSame('hello', (new Parser())->parse('hello', inline: true));
	}

	public function testParseNormalizesLineBreaks(): void
	{
		$parser = new Parser();

		// CRLF and CR are standardized to LF before parsing
		$expected = $parser->parse("a\nb");
		$this->assertSame($expected, $parser->parse("a\r\nb"));
		$this->assertSame($expected, $parser->parse("a\rb"));
	}

	public function testParseBreaks(): void
	{
		// with breaks enabled a single newline becomes a <br>
		$this->assertSame("<p>a<br />\nb</p>", (new Parser(breaks: true))->parse("a\nb"));

		// without breaks the newline is kept as-is
		$this->assertSame("<p>a\nb</p>", (new Parser(breaks: false))->parse("a\nb"));
	}

	public function testParseSafeMode(): void
	{
		// raw HTML is escaped in safe mode
		$this->assertSame(
			'<p>&lt;div&gt;x&lt;/div&gt;</p>',
			(new Parser(safe: true))->parse('<div>x</div>')
		);

		// and passed through otherwise
		$this->assertSame(
			'<div>x</div>',
			(new Parser(safe: false))->parse('<div>x</div>')
		);
	}

	public function testParseResetsState(): void
	{
		// per-document state (footnote numbering, references) is reset
		// each parse, so a reused instance does not leak between calls
		$parser = new Parser();
		$input  = "A ref.[^1]\n\n[^1]: the note";

		$first  = $parser->parse($input);
		$second = $parser->parse($input);

		$this->assertSame($first, $second);
	}
}
