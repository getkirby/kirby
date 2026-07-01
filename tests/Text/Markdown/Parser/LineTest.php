<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Cursor::class)]
#[CoversClass(Line::class)]
class LineTest extends TestCase
{
	public function testBody(): void
	{
		$line = new Line(['  foo', 'bar']);

		// the body keeps the leading indent
		$this->assertSame('  foo', $line->body());
	}

	public function testBodyExpandsLeadingTab(): void
	{
		$line = new Line(["\tfoo"]);

		// a leading tab expands to a full four-column tab stop
		$this->assertSame('    foo', $line->body());
		$this->assertSame(4, $line->indent());
		$this->assertSame('foo', $line->text());
	}

	public function testBodyExpandsMidLineTab(): void
	{
		// a tab expands to the next tab stop, not a fixed width
		$line = new Line(["ab\tcd"]);
		$this->assertSame('ab  cd', $line->body());

		// a tab on a tab stop expands to a full four spaces
		$line = new Line(["abcd\tef"]);
		$this->assertSame('abcd    ef', $line->body());
	}

	public function testIndent(): void
	{
		$line = new Line(['   foo']);
		$this->assertSame(3, $line->indent());

		$line = new Line(['foo']);
		$this->assertSame(0, $line->indent());
	}

	public function testIsBlank(): void
	{
		$line = new Line(['', '   ', "\t", 'foo']);
		$this->assertTrue($line->isBlank());
		$this->assertTrue($line->isBlank(1));
		$this->assertTrue($line->isBlank(2));
		$this->assertFalse($line->isBlank(3));
		$this->assertFalse($line->isBlank(10));
	}

	public function testMarker(): void
	{
		$line = new Line(['# heading']);
		$this->assertSame('#', $line->marker());

		$line = new Line(['   > quote']);
		$this->assertSame('>', $line->marker());

		$line = new Line(['']);
		$this->assertSame('', $line->marker());
	}

	public function testMatches(): void
	{
		$line = new Line(['## Heading']);
		$this->assertTrue($line->matches('/^#{1,6}\s/'));
		$this->assertFalse($line->matches('/^\d/'));

		$line = new Line(['    ## Heading']);
		$this->assertTrue($line->matches('/^#{1,6}\s/'));
	}

	public function testNext(): void
	{
		$line = new Line(['foo', 'bar']);
		$this->assertSame('foo', $line->text());

		$line->next();
		$this->assertSame('bar', $line->text());
	}

	public function testNextReloadsIndent(): void
	{
		$line = new Line(['foo', '   bar']);
		$this->assertSame(0, $line->indent());

		$line->next();
		$this->assertSame(3, $line->indent());
		$this->assertSame('bar', $line->text());
	}

	public function testSlice(): void
	{
		$line = new Line(['hello world']);
		$this->assertSame('hello', $line->slice(0, 5));
		$this->assertSame('world', $line->slice(6));

		$line = new Line(['   hello']);
		$this->assertSame('he', $line->slice(0, 2));
	}

	public function testStartsWith(): void
	{
		$line = new Line(['   > quote']);
		$this->assertTrue($line->startsWith('>'));
		$this->assertFalse($line->startsWith(' '));
	}

	public function testText(): void
	{
		$line = new Line(['   foo']);
		$this->assertSame('foo', $line->text());
	}

	public function testValid(): void
	{
		$line = new Line(['a', 'b']);
		$this->assertTrue($line->valid());

		$line->next();
		$this->assertTrue($line->valid());

		$line->next();
		$this->assertFalse($line->valid());
	}

	public function testValidEmpty(): void
	{
		$line = new Line([]);

		// with no source lines the cursor is invalid from the start
		$this->assertFalse($line->valid());
		$this->assertSame('', $line->body());
		$this->assertSame(0, $line->indent());
	}
}
