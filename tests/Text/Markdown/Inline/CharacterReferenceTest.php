<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CharacterReference::class)]
class CharacterReferenceTest extends TestCase
{
	protected CharacterReference $inline;

	public function setUp(): void
	{
		$this->inline = new CharacterReference(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(CharacterReference::class);
	}

	public function testConsumeNamedEntity(): void
	{
		$phrase = new Phrase('&amp;');
		$node   = $this->inline->consume($phrase);

		$this->assertInstanceOf(Text::class, $node);
		$this->assertSame('&', $node->text);
		$this->assertFalse($node->hasBreak());
		$this->assertSame('', $phrase->after());

		$this->assertSame('©', $this->inline->consume(new Phrase('&copy;'))->text);
	}

	public function testConsumeDecimalEntity(): void
	{
		$this->assertSame('#', $this->inline->consume(new Phrase('&#35;'))->text);
		$this->assertSame('©', $this->inline->consume(new Phrase('&#169;'))->text);
	}

	public function testConsumeHexEntity(): void
	{
		$this->assertSame('"', $this->inline->consume(new Phrase('&#x22;'))->text);
		$this->assertSame('©', $this->inline->consume(new Phrase('&#XA9;'))->text);
	}

	public function testConsumeInvalidCodepoint(): void
	{
		// the null code point is replaced with U+FFFD
		$this->assertSame("\u{FFFD}", $this->inline->consume(new Phrase('&#0;'))->text);
	}

	public function testConsumeUnknownEntity(): void
	{
		// a name that is not in the HTML5 list is not an entity
		$this->assertFalse($this->inline->consume(new Phrase('&MadeUpEntity;')));
	}

	public function testConsumeTooManyDigits(): void
	{
		// a decimal reference allows at most seven digits
		$this->assertFalse($this->inline->consume(new Phrase('&#87654321;')));
	}

	public function testConsumeSpaceAfterAmpersand(): void
	{
		// `& amp;` is a literal ampersand, not an entity
		$this->assertFalse($this->inline->consume(new Phrase('& amp;')));
	}

	public function testConsumeMissingSemicolon(): void
	{
		$this->assertFalse($this->inline->consume(new Phrase('&amp')));
	}

	public function testConsumeEmptyEntity(): void
	{
		// `&;` has no entity name
		$this->assertFalse($this->inline->consume(new Phrase('&;')));
	}
}
