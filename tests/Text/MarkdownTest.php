<?php

namespace Kirby\Text;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Text\Markdown
 */
class MarkdownTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	/**
	 * @covers ::defaults
	 */
	public function testDefaults()
	{
		$markdown = new Markdown();

		$this->assertSame([
			'breaks' => true,
			'extra'  => false,
			'safe'   => false,
		], $markdown->defaults());
	}

	/**
	 * @covers ::__construct
	 */
	public function testWithOptions()
	{
		$markdown = new Markdown([
			'extra'  => true,
			'breaks' => false
		]);

		$this->assertInstanceOf(Markdown::class, $markdown);
	}

	/**
	 * @covers ::__construct
	 */
	public function testSafeModeDisabled()
	{
		$markdown = new Markdown([
			'safe' => false
		]);

		$this->assertSame('<div>Custom HTML</div>', $markdown->parse('<div>Custom HTML</div>'));
	}

	/**
	 * @covers ::__construct
	 */
	public function testSafeModeEnabled()
	{
		$markdown = new Markdown([
			'safe' => true
		]);

		$this->assertSame('<p>&lt;div&gt;Custom HTML&lt;/div&gt;</p>', $markdown->parse('<div>Custom HTML</div>'));
	}

	/**
	 * @covers ::parse
	 */
	public function testParse()
	{
		$markdown = new Markdown();
		$md       = file_get_contents(static::FIXTURES . '/markdown.md');
		$html     = file_get_contents(static::FIXTURES . '/markdown.html');
		$this->assertSame($html, $markdown->parse($md));
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithExtra()
	{
		$markdown = new Markdown(['extra' => true]);
		$md       = file_get_contents(static::FIXTURES . '/markdown.md');
		$html     = file_get_contents(static::FIXTURES . '/markdownextra.html');
		$this->assertSame($html, $markdown->parse($md));
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithoutBreaks()
	{
		$markdown = new Markdown(['breaks' => false]);
		$md       = file_get_contents(static::FIXTURES . '/markdown.md');
		$html     = file_get_contents(static::FIXTURES . '/markdownbreaks.html');
		$this->assertSame($html, $markdown->parse($md));
	}
}
