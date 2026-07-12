<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\TestCase;
use Kirby\Text\Markdown\Parser;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Brackets::class)]
class BracketsTest extends TestCase
{
	protected Parser $parser;

	public function setUp(): void
	{
		$this->parser = new Parser();
	}

	public function testResolvesLink(): void
	{
		$this->assertSame(
			'<a href="/url" title="title">text</a>',
			$this->parser->parse('[text](/url "title")', true)
		);
	}

	public function testResolvesImage(): void
	{
		$this->assertSame(
			'<img src="/img.jpg" alt="alt" />',
			$this->parser->parse('![alt](/img.jpg)', true)
		);
	}

	public function testResolvesImageInsideLink(): void
	{
		// an image opener stays active inside a link, so the image resolves
		$this->assertSame(
			'<a href="/l"><img src="/i.jpg" alt="a" /></a>',
			$this->parser->parse('[![a](/i.jpg)](/l)', true)
		);
	}

	public function testClosingBracketWithoutOpenerStaysLiteral(): void
	{
		// a `]` with no matching `[` is emitted verbatim
		$this->assertSame('a ] b', $this->parser->parse('a ] b', true));
	}

	public function testUnresolvedBracketStaysLiteral(): void
	{
		// a `[…]` with neither a destination nor a matching reference
		// definition stays literal
		$this->assertSame('[text]', $this->parser->parse('[text]', true));
	}

	public function testNestedLinkOpenerIsDisabled(): void
	{
		// a formed link disables every earlier link opener, so the outer
		// `[` never becomes a link
		$this->assertSame(
			'[<a href="/b">a</a>](/c)',
			$this->parser->parse('[[a](/b)](/c)', true)
		);
	}
}
