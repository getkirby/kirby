<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\TestCase;
use Kirby\Text\Markdown\AST\Element;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Attributes::class)]
class AttributesTest extends TestCase
{
	public function testApply(): void
	{
		$element = new Element(name: 'h1', content: 'Heading {#intro .lead}');
		$result  = Attributes::apply($element, '[ #]*', '[ ]*');

		// the trailing block is stripped off the content
		$this->assertSame('Heading', $result->content);
		$this->assertSame(['id' => 'intro', 'class' => 'lead'], $result->attributes);
	}

	public function testApplyNoMatch(): void
	{
		$element = new Element(name: 'h1', content: 'Just a heading');
		$result  = Attributes::apply($element, '[ #]*', '[ ]*');

		// without a trailing block the element is untouched
		$this->assertSame('Just a heading', $result->content);
		$this->assertSame([], $result->attributes);
	}

	public function testApplyRespectsAfter(): void
	{
		// the `$after` fragment must still match what follows the block
		$element = new Element(name: 'h1', content: 'Heading {#intro} trailing');
		$result  = Attributes::apply($element, '[ #]*', '[ ]*');

		// "trailing" is not allowed by `[ ]*`, so nothing is stripped
		$this->assertSame('Heading {#intro} trailing', $result->content);
		$this->assertSame([], $result->attributes);
	}

	public function testApplyReturnsElement(): void
	{
		$element = new Element(name: 'h1', content: 'Heading');
		$result  = Attributes::apply($element, '[ #]*', '[ ]*');
		$this->assertSame($element, $result);
	}

	public function testParseClass(): void
	{
		$this->assertSame(['class' => 'lead'], Attributes::parse('.lead'));
	}

	public function testParseEmpty(): void
	{
		$this->assertSame([], Attributes::parse(''));
	}

	public function testParseExtraWhitespace(): void
	{
		// runs of spaces are ignored
		$this->assertSame(
			['id' => 'intro', 'class' => 'lead'],
			Attributes::parse('#intro   .lead')
		);
	}

	public function testParseId(): void
	{
		$this->assertSame(['id' => 'intro'], Attributes::parse('#intro'));
	}

	public function testParseLastIdWins(): void
	{
		$this->assertSame(['id' => 'second'], Attributes::parse('#first #second'));
	}

	public function testParseMultipleClasses(): void
	{
		// classes are collected and joined into a single attribute
		$this->assertSame(
			['id' => 'intro', 'class' => 'lead big'],
			Attributes::parse('#intro .lead .big')
		);
	}
}
