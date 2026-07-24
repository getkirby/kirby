<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\TestCase;
use Kirby\Text\Markdown;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ContainerBlock::class)]
class ContainerBlockTest extends TestCase
{
	protected function parse(string $markdown): string
	{
		return (new Markdown())->parse($markdown);
	}

	public function testLazyContinuation(): void
	{
		// a marker-less line continues the blockquote's trailing paragraph
		$this->assertStringContainsString(
			"<blockquote>\n<p>a<br />\nb</p>\n</blockquote>",
			$this->parse("> a\nb")
		);
	}

	public function testLazyContinuationIntoNestedList(): void
	{
		// findTrailingParagraph descends into the nested list's content to
		// reach the trailing paragraph the line continues
		$this->assertStringContainsString(
			'<li>a<br />',
			$this->parse("> - a\nb")
		);
	}

	public function testLazyContinuationIntoNestedQuote(): void
	{
		// the trailing paragraph lives inside a nested quote whose content
		// is still deferred as raw lines when the probe inspects it
		$this->assertStringContainsString(
			"<p>a<br />\nb</p>",
			$this->parse("> > a\nb")
		);
	}

	public function testHeadingIsNotContinued(): void
	{
		// a heading is not a paragraph, so a following marker-less line
		// starts a new block outside the quote
		$out = $this->parse("> # h\nb");

		$this->assertStringContainsString('<h1>h</h1>', $out);
		$this->assertStringContainsString('</blockquote>', $out);
		$this->assertStringEndsWith('<p>b</p>', $out);
	}

	public function testThematicBreakTrailingIsNotContinued(): void
	{
		// a thematic break has no content to continue, so the line does not
		// extend the quote
		$out = $this->parse("> ---\nb");

		$this->assertStringContainsString('<hr />', $out);
		$this->assertStringContainsString('<p>b</p>', $out);
	}
}
