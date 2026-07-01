<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Email::class)]
class EmailTest extends TestCase
{
	protected Email $span;

	public function setUp(): void
	{
		$this->span = new Email(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Email::class);
	}

	public function testConsume(): void
	{
		$phrase = new Phrase('<email@getkirby.com>');
		$node   = $this->span->consume($phrase);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('a', $node->name);

		// the href gets a mailto: scheme, the label does not
		$this->assertSame(['href' => 'mailto:email@getkirby.com'], $node->attributes);
		$this->assertInstanceOf(Text::class, $node->children[0]);
		$this->assertSame('email@getkirby.com', $node->children[0]->text);
		$this->assertFalse($node->hasBreak());
	}

	public function testConsumeExplicitMailto(): void
	{
		// an explicit mailto: is kept as-is in both href and label
		$phrase = new Phrase('<mailto:email@getkirby.com>');
		$node   = $this->span->consume($phrase);

		$this->assertSame(['href' => 'mailto:email@getkirby.com'], $node->attributes);
		$this->assertSame('mailto:email@getkirby.com', $node->children[0]->text);
	}

	public function testConsumeUnclosed(): void
	{
		$phrase = new Phrase('<email@getkirby.com');

		$this->assertFalse($this->span->consume($phrase));
	}

	public function testConsumeNotAnEmail(): void
	{
		$phrase = new Phrase('<https://example.com>');

		$this->assertFalse($this->span->consume($phrase));
	}
}
