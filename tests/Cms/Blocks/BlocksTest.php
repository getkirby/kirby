<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class BlocksTest extends TestCase
{
    protected $page;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
        ]);

        $this->page = new Page(['slug' => 'test']);
    }

    public function testToHtml()
    {
        $blocks = Blocks::factory([
            [
                'content' => [
                    'text' => 'Hello world'
                ],
                'type' => 'heading'
            ],
            [
                'content' => [
                    'text' => 'Nice blocks'
                ],
                'type' => 'text'
            ],
        ]);

        $expected = "<h1>Hello world</h1>\n<p>Nice blocks</p>\n";

        $this->assertSame($expected, $blocks->toHtml());
    }

    public function testToHtmlWithCustomSnippets()
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null',
                'snippets' => __DIR__ . '/fixtures/snippets'
            ],
        ]);

        $blocks = Blocks::factory([
            [
                'content' => [
                    'text' => 'Hello world'
                ],
                'type' => 'heading'
            ],
            [
                'content' => [
                    'text' => 'Nice blocks'
                ],
                'type' => 'text'
            ],
        ]);

        $expected = "<h1 class=\"custom-heading\">Hello world</h1>\n<p class=\"custom-text\">Nice blocks</p>\n";

        $this->assertSame($expected, $blocks->toHtml());
    }
}
