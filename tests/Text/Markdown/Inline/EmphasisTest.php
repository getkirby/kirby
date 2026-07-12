<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Delimiter;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Emphasis::class)]
class EmphasisTest extends TestCase
{
	protected Emphasis $inline;

	public function setUp(): void
	{
		$this->inline = new Emphasis(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Emphasis::class);
	}

	public function testPair(): void
	{
		$this->assertSame([1, 'em'], $this->inline->pair(1));
		$this->assertSame([2, 'strong'], $this->inline->pair(2));
		$this->assertSame([2, 'strong'], $this->inline->pair(5));
	}

	public function testOpenClose(): void
	{
		// `*` uses the plain flanking rule
		$this->assertSame([true, false], $this->inline->openClose('', 'e'));
		$this->assertSame([false, true], $this->inline->openClose('e', ''));
	}

	public function testConsumeAsterisk(): void
	{
		$phrase = new Phrase('*em*');
		$node   = $this->inline->consume($phrase);

		$this->assertInstanceOf(Delimiter::class, $node);
		$this->assertSame('*', $node->marker);
		$this->assertSame(1, $node->length);
		$this->assertTrue($node->canOpen);
		$this->assertFalse($node->canClose);
		$this->assertSame('em*', $phrase->after());
	}

	public function testConsumeStrongRun(): void
	{
		// a run of two markers is emitted as a single delimiter
		$phrase = new Phrase('**strong**');
		$node   = $this->inline->consume($phrase);

		$this->assertInstanceOf(Delimiter::class, $node);
		$this->assertSame(2, $node->length);
		$this->assertSame('strong**', $phrase->after());
	}

	public function testConsumeInert(): void
	{
		// a marker flanked by whitespace (here the line boundary on
		// both sides) can neither open nor close and later renders
		// literally
		$node = $this->inline->consume(new Phrase('*'));

		$this->assertInstanceOf(Delimiter::class, $node);
		$this->assertFalse($node->canOpen);
		$this->assertFalse($node->canClose);
	}

	public function testParse(): void
	{
		$parser = new Parser();

		$this->assertSame('<em>em</em>', $parser->parse('*em*', true));
		$this->assertSame('<strong>strong</strong>', $parser->parse('**strong**', true));
		$this->assertSame('<em><em>x</em></em>', $parser->parse('*_x_*', true));

		// an unclosed run stays literal
		$this->assertSame('*not closed', $parser->parse('*not closed', true));

		// no emphasis across whitespace
		$this->assertSame('a * b *', $parser->parse('a * b *', true));
	}

	public function testParseGenderStar(): void
	{
		$parser = new Parser();

		// an intraword `*` pair may not span whitespace: the German gender
		// star stays literal instead of emphasizing across words (a
		// deliberate deviation from CommonMark)
		$this->assertSame(
			'Lehrer*innen und Schüler*innen',
			$parser->parse('Lehrer*innen und Schüler*innen', true)
		);

		// a whitespace-free intraword span still emphasizes
		$this->assertSame('un<em>believ</em>able', $parser->parse('un*believ*able', true));

		// a boundary before the opener is not intraword, so ordinary
		// phrase emphasis is unaffected
		$this->assertSame('a <em>b c</em> d', $parser->parse('a *b c* d', true));
	}

	public function testParseNonAsciiFlanking(): void
	{
		$parser = new Parser();

		// non-ASCII punctuation (guillemets) around a run takes the
		// Unicode classification path
		$this->assertSame('«<em>em</em>»', $parser->parse('«*em*»', true));

		// a non-breaking space is Unicode whitespace, so the run it
		// flanks can neither open nor close and stays literal
		$this->assertSame("\u{A0}*\u{A0}", $parser->parse("\u{A0}*\u{A0}", true));
	}
}
