<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinkTarget::class)]
class LinkTargetTest extends TestCase
{
	public function testDestinationBare(): void
	{
		$this->assertSame(['http://example.com', 18], LinkTarget::destination('http://example.com)', 0));

		// a stop with nothing before it (here a leading `)`) is malformed
		$this->assertNull(LinkTarget::destination(')', 0));
	}

	public function testDestinationAngleBracketed(): void
	{
		$this->assertSame(['a b', 5], LinkTarget::destination('<a b>', 0));

		// an unescaped `<` or a line break inside `<…>` is malformed
		$this->assertNull(LinkTarget::destination("<a\nb>", 0));
		$this->assertNull(LinkTarget::destination('<a<b>', 0));

		// a backslash escapes punctuation (here `>`) inside the brackets
		$this->assertSame(['a>b', 6], LinkTarget::destination('<a\\>b>', 0));

		// reaching the end without a closing `>` is malformed
		$this->assertNull(LinkTarget::destination('<abc', 0));
	}

	public function testDestinationBalancedParens(): void
	{
		$this->assertSame(['a(b)c', 5], LinkTarget::destination('a(b)c)', 0));

		// an unbalanced closing paren ends the destination
		$this->assertSame(['ab', 2], LinkTarget::destination('ab)c', 0));

		// a space ends the balanced run
		$this->assertSame(['a(b)', 4], LinkTarget::destination('a(b) c', 0));
	}

	public function testDestinationEscapes(): void
	{
		// a backslash escapes ASCII punctuation and is consumed
		$this->assertSame(['a(b', 4], LinkTarget::destination('a\\(b)', 0));

		// an unbalanced opening paren is malformed
		$this->assertNull(LinkTarget::destination('a(b', 0));
	}

	public function testParse(): void
	{
		$this->assertSame(
			['href' => 'http://example.com', 'title' => 'Title', 'length' => 28],
			LinkTarget::parse('(http://example.com "Title")')
		);

		// no title
		$this->assertSame(
			['href' => 'http://example.com', 'title' => null, 'length' => 20],
			LinkTarget::parse('(http://example.com)')
		);

		// empty destination
		$this->assertSame(
			['href' => '', 'title' => null, 'length' => 2],
			LinkTarget::parse('()')
		);
	}

	public function testParseNotATarget(): void
	{
		// must start with `(`
		$this->assertNull(LinkTarget::parse('http://example.com'));

		// an unbalanced/unterminated target
		$this->assertNull(LinkTarget::parse('(http://example.com'));

		// a malformed destination (an unclosed `<…>`) after the `(`
		$this->assertNull(LinkTarget::parse('(<abc'));
	}

	public function testTitle(): void
	{
		$this->assertSame(['Title', 7], LinkTarget::title('"Title"', 0));
		$this->assertSame(['Title', 7], LinkTarget::title("'Title'", 0));
		$this->assertSame(['Title', 7], LinkTarget::title('(Title)', 0));

		// not a title delimiter
		$this->assertNull(LinkTarget::title('Title', 0));

		// a paren title may not contain an unescaped `(` — on the fast path
		$this->assertNull(LinkTarget::title('(a(b)', 0));

		// …and the same inside the per-byte loop (reached here via a `\`)
		$this->assertNull(LinkTarget::title('(a\\)(', 0));
	}

	public function testTitleEscaped(): void
	{
		// a backslash escape drops into the per-byte unescape loop and
		// consumes the escaped quote as a literal
		$this->assertSame(['a " b', 8], LinkTarget::title('"a \\" b"', 0));
	}

	public function testTitleUnterminated(): void
	{
		// entering the unescape loop (via a `\`) but never reaching the
		// closer is unterminated
		$this->assertNull(LinkTarget::title('"a\\b', 0));
	}
}
