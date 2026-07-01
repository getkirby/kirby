<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SpecialCharacter::class)]
class SpecialCharacterTest extends TestCase
{
	protected SpecialCharacter $span;

	public function setUp(): void
	{
		$this->span = new SpecialCharacter(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(SpecialCharacter::class);
	}

	public function testConsumeNamedEntity(): void
	{
		$phrase = new Phrase('&amp;');
		$node   = $this->span->consume($phrase);

		$this->assertInstanceOf(Html::class, $node);
		$this->assertSame('&amp;', $node->html);
		$this->assertFalse($node->hasBreak());
		$this->assertSame(5, $phrase->consumed());
	}

	public function testConsumeNumericEntity(): void
	{
		$phrase = new Phrase('&#169;');
		$node   = $this->span->consume($phrase);

		$this->assertSame('&#169;', $node->html);
	}

	public function testConsumeSpaceAfterAmpersand(): void
	{
		// `& amp;` is a literal ampersand, not an entity
		$phrase = new Phrase('& amp;');

		$this->assertFalse($this->span->consume($phrase));
	}

	public function testConsumeMissingSemicolon(): void
	{
		$phrase = new Phrase('&amp');

		$this->assertFalse($this->span->consume($phrase));
	}

	public function testConsumeEmptyEntity(): void
	{
		// `&;` has no entity name
		$phrase = new Phrase('&;');

		$this->assertFalse($this->span->consume($phrase));
	}
}
