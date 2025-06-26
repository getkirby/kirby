<?php

namespace Kirby\Text;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Markdown::class)]
class MarkdownTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	public function testDefaults(): void
	{
		$markdown = new Markdown();

		$this->assertSame([
			'breaks' => true,
			'extra'  => false,
			'safe'   => false,
		], $markdown->defaults());
	}

	public function testWithOptions(): void
	{
		$markdown = new Markdown([
			'extra'  => true,
			'breaks' => false
		]);

		$this->assertInstanceOf(Markdown::class, $markdown);
	}

	public function testSafeModeDisabled(): void
	{
		$markdown = new Markdown([
			'safe' => false
		]);

		$this->assertSame('<div>Custom HTML</div>', $markdown->parse('<div>Custom HTML</div>'));
	}

	public function testSafeModeEnabled(): void
	{
		$markdown = new Markdown([
			'safe' => true
		]);

		$this->assertSame('<p>&lt;div&gt;Custom HTML&lt;/div&gt;</p>', $markdown->parse('<div>Custom HTML</div>'));
	}

	public function testParse(): void
	{
		$markdown = new Markdown();
		$md       = file_get_contents(static::FIXTURES . '/markdown.md');
		$html     = file_get_contents(static::FIXTURES . '/markdown.html');
		$this->assertSame($html, $markdown->parse($md));
	}

	public function testParseInline(): void
	{
		$markdown = new Markdown();
		$md       = file_get_contents(static::FIXTURES . '/inline.md');
		$html     = file_get_contents(static::FIXTURES . '/inline.html');
		$this->assertSame($html, $markdown->parse($md, true));
	}

	public function testParseWithExtra(): void
	{
		$markdown = new Markdown(['extra' => true]);
		$md       = file_get_contents(static::FIXTURES . '/markdown.md');
		$html     = file_get_contents(static::FIXTURES . '/markdownextra.html');
		$this->assertSame($html, $markdown->parse($md));
	}

	public function testParseWithoutBreaks(): void
	{
		$markdown = new Markdown(['breaks' => false]);
		$md       = file_get_contents(static::FIXTURES . '/markdown.md');
		$html     = file_get_contents(static::FIXTURES . '/markdownbreaks.html');
		$this->assertSame($html, $markdown->parse($md));
	}
}
