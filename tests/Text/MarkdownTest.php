<?php

namespace Kirby\Text;

use PHPUnit\Framework\TestCase;

class MarkdownTest extends TestCase
{
    public const FIXTURES = __DIR__ . '/fixtures';

    public function testDefaults()
    {
        $markdown = new Markdown();

        $this->assertSame([
            'breaks' => true,
            'extra'  => false,
            'safe'   => false,
        ], $markdown->defaults());
    }

    public function testWithOptions()
    {
        $markdown = new Markdown([
            'extra'  => true,
            'breaks' => false
        ]);

        $this->assertInstanceOf(Markdown::class, $markdown);
    }

    public function testSafeModeDisabled()
    {
        $markdown = new Markdown([
            'safe' => false
        ]);

        $this->assertSame('<div>Custom HTML</div>', $markdown->parse('<div>Custom HTML</div>'));
    }

    public function testSafeModeEnabled()
    {
        $markdown = new Markdown([
            'safe' => true
        ]);

        $this->assertSame('<p>&lt;div&gt;Custom HTML&lt;/div&gt;</p>', $markdown->parse('<div>Custom HTML</div>'));
    }

    public function testParse()
    {
        $markdown = new Markdown();
        $md       = file_get_contents(static::FIXTURES . '/markdown.md');
        $html     = file_get_contents(static::FIXTURES . '/markdown.html');
        $this->assertSame($html, $markdown->parse($md));
    }

    public function testParseWithExtra()
    {
        $markdown = new Markdown(['extra' => true]);
        $md       = file_get_contents(static::FIXTURES . '/markdown.md');
        $html     = file_get_contents(static::FIXTURES . '/markdownextra.html');
        $this->assertSame($html, $markdown->parse($md));
    }

    public function testParseWithoutBreaks()
    {
        $markdown = new Markdown(['breaks' => false]);
        $md       = file_get_contents(static::FIXTURES . '/markdown.md');
        $html     = file_get_contents(static::FIXTURES . '/markdownbreaks.html');
        $this->assertSame($html, $markdown->parse($md));
    }
}
