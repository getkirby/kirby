<?php

namespace Kirby\Text;

use PHPUnit\Framework\TestCase;

class MarkdownTest extends TestCase
{
    const FIXTURES = __DIR__ . '/fixtures';

    public function testDefaults()
    {
        $markdown = new Markdown();

        $this->assertSame([
            'extra'  => false,
            'breaks' => true
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
