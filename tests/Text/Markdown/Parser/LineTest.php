<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Cursor::class)]
#[CoversClass(Line::class)]
class LineTest extends TestCase
{
	public function testBack(): void
	{
		$line = new Line(['a', 'b', 'c']);
		$line->next();
		$line->next();
		$this->assertSame('c', $line->text());

		// stepping back re-reads an earlier line
		$line->back(2);
		$this->assertSame('a', $line->text());
	}

	public function testBody(): void
	{
		$line = new Line(['  foo', 'bar']);

		// the body keeps the leading indent
		$this->assertSame('  foo', $line->body());
	}

	public function testBodyKeepsRawTabs(): void
	{
		$line = new Line(["\tfoo"]);

		// body() keeps the raw line — tabs are not expanded in content —
		// while indent() and text() still measure tab-expanded columns
		// (a leading tab is a full four-column tab stop)
		$this->assertSame("\tfoo", $line->body());
		$this->assertSame(4, $line->indent());
		$this->assertSame('foo', $line->text());
	}

	public function testContent(): void
	{
		// dedent removes leading whitespace columns but keeps the raw tabs
		// of the content past the indentation
		$line = new Line(["\tfoo\tbar"]);
		$this->assertSame("foo\tbar", $line->content(4));

		// a tab partly inside the cut leaves its surplus columns as spaces
		$line = new Line(["\t\tbar"]);
		$this->assertSame('  bar', $line->content(6));

		// only the whitespace that actually exists is removed
		$line = new Line(['  foo']);
		$this->assertSame('foo', $line->content(4));
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

	public function testPeek(): void
	{
		// peek returns the de-indented, tab-expanded text of a later
		// line without advancing the cursor
		$line = new Line(['foo', '  bar', "\tbaz"]);

		$this->assertSame('bar', $line->peek(1));
		$this->assertSame('baz', $line->peek(2));
		$this->assertNull($line->peek(3));
		$this->assertSame('foo', $line->text());
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

	public function testTextSlice(): void
	{
		$line = new Line(['hello world']);
		$this->assertSame('hello', $line->text(0, 5));
		$this->assertSame('world', $line->text(6));

		$line = new Line(['   hello']);
		$this->assertSame('he', $line->text(0, 2));
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
