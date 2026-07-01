<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Cursor::class)]
#[CoversClass(Phrase::class)]
class PhraseTest extends TestCase
{
	public function testAfter(): void
	{
		$phrase = new Phrase('a**b');
		$phrase->seek('*');
		$phrase->take(2);
		$this->assertSame('b', $phrase->after());
	}

	public function testAt(): void
	{
		$phrase = new Phrase('a*b');
		$phrase->seek('*');

		$this->assertSame('*', $phrase->at(0));
		$this->assertSame('b', $phrase->at(1));
		$this->assertSame('a', $phrase->at(-1));

		// past the end yields an empty string
		$this->assertSame('', $phrase->at(10));
	}

	public function testConsumed(): void
	{
		$phrase = new Phrase('a**b');
		$phrase->seek('*');
		$phrase->take(2);

		$this->assertSame(2, $phrase->consumed());
	}

	public function testContext(): void
	{
		$phrase = new Phrase('abcdef');

		// the whole text before anything is emitted
		$this->assertSame('abcdef', $phrase->context());
	}

	public function testContextAfterFlush(): void
	{
		$phrase = new Phrase('lead*rest');
		$phrase->seek('*');
		$phrase->take(1);
		$phrase->flush();

		// the emit position moved past the match
		$this->assertSame('rest', $phrase->context());
	}

	public function testExtend(): void
	{
		$phrase = new Phrase('a**b');
		$phrase->seek('*');
		$phrase->take(1);
		$phrase->extend(1);

		$this->assertSame(2, $phrase->consumed());
	}

	public function testFlush(): void
	{
		$phrase = new Phrase('a*bc*d');
		$phrase->seek('*');
		$phrase->take(1);
		$phrase->flush();

		// with the emit position advanced, the next match's lead
		// is measured from just after the flushed match
		$phrase->seek('*');
		$phrase->take(1);
		$this->assertSame('bc', $phrase->lead());
	}

	public function testHas(): void
	{
		$phrase = new Phrase('foo*bar');
		$phrase->seek('*');

		// has() operates on the marker's rest
		$this->assertTrue($phrase->has('bar'));
		$this->assertFalse($phrase->has('foo'));
	}

	public function testLead(): void
	{
		$phrase = new Phrase('lead*match');
		$phrase->seek('*');
		$phrase->take(1);

		// the unmarked text before the match
		$this->assertSame('lead', $phrase->lead());
	}

	public function testMarker(): void
	{
		$phrase = new Phrase('foo*bar');
		$phrase->seek('*');

		$this->assertSame('*', $phrase->marker());
	}

	public function testMatch(): void
	{
		$phrase = new Phrase('x**bold**');
		$phrase->seek('*');

		// match() is anchored to the marker's rest
		$match = $phrase->match('/^\*\*(.+?)\*\*/');
		$this->assertSame('bold', $match[1]);
	}

	public function testMatched(): void
	{
		$phrase = new Phrase('abc*xyz');
		$phrase->seek('*');

		// a match at or before the marker counts as matched
		$phrase->reach(1, 2);
		$this->assertTrue($phrase->matched());

		// a match that only begins after the marker does not
		$phrase->reach(5, 1);
		$this->assertFalse($phrase->matched());
	}

	public function testReach(): void
	{
		$phrase = new Phrase('abc*xyz');
		$phrase->seek('*');

		// records a match one byte after the emit position, two bytes long
		$phrase->reach(1, 2);
		$this->assertSame(2, $phrase->consumed());
		$this->assertSame('a', $phrase->lead());
	}

	public function testSeek(): void
	{
		$phrase = new Phrase('abc*def');
		$this->assertTrue($phrase->seek('*'));
		$this->assertSame('*', $phrase->marker());

		// no marker character remains
		$phrase = new Phrase('abcdef');
		$this->assertFalse($phrase->seek('*'));

		// stops at whichever marker comes first
		$phrase = new Phrase('ab_cd*ef');
		$this->assertTrue($phrase->seek('*_'));
		$this->assertSame('_', $phrase->marker());
	}

	public function testSkip(): void
	{
		$phrase = new Phrase('ab*cd');
		$phrase->seek('*');

		// returns the text up to and including the unclaimed marker
		$this->assertSame('ab*', $phrase->skip());
		$this->assertSame('cd', $phrase->context());
	}

	public function testSlice(): void
	{
		$phrase = new Phrase('abc*defgh');
		$phrase->seek('*');

		$this->assertSame('def', $phrase->slice(1, 3));
		$this->assertSame('*defgh', $phrase->slice(0));
	}

	public function testTake(): void
	{
		$phrase = new Phrase('a**b');
		$phrase->seek('*');
		$phrase->take(2);

		$this->assertSame(2, $phrase->consumed());
		$this->assertSame('b', $phrase->after());

		// a string argument is measured by its byte length
		$phrase = new Phrase('foo**bar');
		$phrase->seek('*');
		$phrase->take('**');
		$this->assertSame(2, $phrase->consumed());
	}

	public function testText(): void
	{
		$phrase = new Phrase('hello*world');
		$phrase->seek('*');

		// the marker's rest: from the marker to the end
		$this->assertSame('*world', $phrase->text());
	}

	public function testTextTracksMarker(): void
	{
		$phrase = new Phrase('a*b*c');
		$phrase->seek('*');
		$this->assertSame('*b*c', $phrase->text());

		// advance past the first marker to the second
		$phrase->take(1);
		$phrase->flush();
		$phrase->seek('*');
		$this->assertSame('*c', $phrase->text());
	}
}
