<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\TestCase;
use Kirby\Text\Markdown\AST\Element;
use PHPUnit\Framework\Attributes\CoversTrait;

class AttributesHost
{
	use Attributes;

	public function callAttributes(
		Element $element,
		string $before,
		string $after = ''
	): Element {
		return $this->attributes($element, $before, $after);
	}

	public function callAttributesFromPhrase(
		Element $element,
		Phrase $phrase
	): Element {
		return $this->attributesFromPhrase($element, $phrase);
	}

	public function callParseAttributes(string $block): array
	{
		return $this->parseAttributes($block);
	}
}

#[CoversTrait(Attributes::class)]
class AttributesTest extends TestCase
{
	protected AttributesHost $host;

	public function setUp(): void
	{
		$this->host = new AttributesHost();
	}

	public function testAttributes(): void
	{
		$element = new Element(name: 'h1', content: 'Heading {#intro .lead}');
		$result  = $this->host->callAttributes($element, '[ #]*', '[ ]*');

		// the trailing block is stripped off the content
		$this->assertSame('Heading', $result->content);
		$this->assertSame(['id' => 'intro', 'class' => 'lead'], $result->attributes);
	}

	public function testAttributesNoMatch(): void
	{
		$element = new Element(name: 'h1', content: 'Just a heading');
		$result  = $this->host->callAttributes($element, '[ #]*', '[ ]*');

		// without a trailing block the element is untouched
		$this->assertSame('Just a heading', $result->content);
		$this->assertSame([], $result->attributes);
	}

	public function testAttributesRespectsAfter(): void
	{
		// the `$after` fragment must still match what follows the block
		$element = new Element(name: 'h1', content: 'Heading {#intro} trailing');
		$result  = $this->host->callAttributes($element, '[ #]*', '[ ]*');

		// "trailing" is not allowed by `[ ]*`, so nothing is stripped
		$this->assertSame('Heading {#intro} trailing', $result->content);
		$this->assertSame([], $result->attributes);
	}

	public function testAttributesReturnsElement(): void
	{
		$element = new Element(name: 'h1', content: 'Heading');
		$result  = $this->host->callAttributes($element, '[ #]*', '[ ]*');
		$this->assertSame($element, $result);
	}

	public function testAttributesFromPhrase(): void
	{
		$phrase = new Phrase('ab{#id .foo}rest');
		$phrase->seek('b');
		$phrase->take(1);

		// after() now begins at the attribute block
		$this->assertSame('{#id .foo}rest', $phrase->after());

		$element = new Element(name: 'a');
		$result  = $this->host->callAttributesFromPhrase($element, $phrase);

		$this->assertSame(['id' => 'id', 'class' => 'foo'], $result->attributes);

		// the match is consumed from the phrase
		$this->assertSame('rest', $phrase->after());
	}

	public function testAttributesFromPhraseMergesExisting(): void
	{
		$phrase = new Phrase('ab{#id .foo}');
		$phrase->seek('b');
		$phrase->take(1);

		$element = new Element(name: 'a', attributes: ['class' => 'orig']);
		$result  = $this->host->callAttributesFromPhrase($element, $phrase);

		// existing keys win over the phrase block (union semantics)
		$this->assertSame(['class' => 'orig', 'id' => 'id'], $result->attributes);
	}

	public function testAttributesFromPhraseNoMatch(): void
	{
		$phrase = new Phrase('ab plain');
		$phrase->seek('b');
		$phrase->take(1);

		$element = new Element(name: 'a');
		$result  = $this->host->callAttributesFromPhrase($element, $phrase);

		// no block, nothing consumed
		$this->assertSame([], $result->attributes);
		$this->assertSame(' plain', $phrase->after());
	}

	public function testParseAttributesId(): void
	{
		$this->assertSame(['id' => 'intro'], $this->host->callParseAttributes('#intro'));
	}

	public function testParseAttributesClass(): void
	{
		$this->assertSame(['class' => 'lead'], $this->host->callParseAttributes('.lead'));
	}

	public function testParseAttributesMultipleClasses(): void
	{
		// classes are collected and joined into a single attribute
		$this->assertSame(
			['id' => 'intro', 'class' => 'lead big'],
			$this->host->callParseAttributes('#intro .lead .big')
		);
	}

	public function testParseAttributesLastIdWins(): void
	{
		$this->assertSame(['id' => 'second'], $this->host->callParseAttributes('#first #second'));
	}

	public function testParseAttributesExtraWhitespace(): void
	{
		// runs of spaces are ignored
		$this->assertSame(
			['id' => 'intro', 'class' => 'lead'],
			$this->host->callParseAttributes('#intro   .lead')
		);
	}

	public function testParseAttributesEmpty(): void
	{
		$this->assertSame([], $this->host->callParseAttributes(''));
	}
}
