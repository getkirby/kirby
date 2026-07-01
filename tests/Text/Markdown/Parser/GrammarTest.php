<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\TestCase;
use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Span;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Grammar::class)]
class GrammarTest extends TestCase
{
	protected Grammar $grammar;

	public function setUp(): void
	{
		$parser        = new Parser();
		$this->grammar = new Grammar($parser);
	}

	public function testBlock(): void
	{
		$block = $this->grammar->block(Block\AtxHeading::class);
		$this->assertInstanceOf(Block\AtxHeading::class, $block);

		// a second lookup returns the cached instance
		$this->assertSame($block, $this->grammar->block(Block\AtxHeading::class));

		// a class that is not a registered block yields null
		$block = $this->grammar->block(Span\Code::class);
		$this->assertNull($block);
	}

	public function testBlocks(): void
	{
		$blocks = $this->grammar->blocks('#');

		// the `#` marker dispatches to the ATX heading block
		$this->assertContainsOnlyInstancesOf(Block::class, $blocks);
		$this->assertSame(Block\AtxHeading::class, $blocks[0]::class);

		// unknown marker
		$this->assertSame([], $this->grammar->blocks('§'));
	}

	public function testMarkers(): void
	{
		$markers = $this->grammar->markers();

		// all registered span markers concatenated into one string
		$this->assertStringContainsString('*', $markers);
		$this->assertStringContainsString('_', $markers);
		$this->assertStringContainsString('`', $markers);
	}

	public function testSpan(): void
	{
		$span = $this->grammar->span(Span\Code::class);
		$this->assertInstanceOf(Span\Code::class, $span);

		$span = $this->grammar->span(Block\AtxHeading::class);
		$this->assertNull($this->grammar->span(Block\AtxHeading::class));
	}

	public function testSpans(): void
	{
		$spans = $this->grammar->spans('`');

		// the backtick marker dispatches to the code span
		$this->assertContainsOnlyInstancesOf(Span::class, $spans);
		$this->assertSame(Span\Code::class, $spans[0]::class);

		// unknown marker
		$this->assertSame([], $this->grammar->spans('§'));
	}

	public function testTransforms(): void
	{
		$transforms = $this->grammar->transforms();

		$this->assertContainsOnlyInstancesOf(Transform::class, $transforms);
		$this->assertCount(3, $transforms);

		// a second call returns the memoized result
		$this->assertSame($transforms, $this->grammar->transforms());
	}
}
