<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BracketedInline::class)]
class BracketedInlineTest extends TestCase
{
	public function testConsumeDeclines(): void
	{
		// bracketed inlines are resolved by the stack, not by marker
		// dispatch, so consume() never starts one
		$inline = new Link(new Parser());

		$this->assertFalse($inline->consume(new Phrase('[a](/b)')));
	}

	public function testElement(): void
	{
		// each bracketed inline builds its own element from the resolution
		$element = (new Link(new Parser()))->element(['attributes' => ['href' => '/b']], []);

		$this->assertSame('a', $element->name);
		$this->assertSame('/b', $element->attributes['href']);
	}
}
