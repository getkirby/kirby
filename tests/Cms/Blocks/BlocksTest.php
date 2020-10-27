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

    public function testConstruct()
    {
        $blocks = new Blocks();

        $a = new Block(['type' => 'a']);
        $b = new Block(['type' => 'b']);

        $blocks->append($a->id(), $a);
        $blocks->append($b->id(), $b);

        $this->assertCount(2, $blocks);
        $this->assertSame($a->id(), $blocks->first()->id());
        $this->assertSame($b->id(), $blocks->last()->id());
    }

    public function testFactoryFromArray()
    {
        $blocks = Blocks::factory([
            [
                'content' => ['text' => 'Heading'],
                'type'    => 'heading',
            ],
            [
                'content' => ['text' => 'Body'],
                'type'    => 'body',
            ]
        ]);

        $this->assertCount(2, $blocks);
        $this->assertSame($blocks, $blocks->first()->siblings());
        $this->assertEquals('Heading', $blocks->first()->text());
        $this->assertEquals('heading', $blocks->first()->type());
        $this->assertEquals('Body', $blocks->last()->text());
        $this->assertEquals('body', $blocks->last()->type());
    }

    public function testParent()
    {
        $blocks = new Blocks([]);

        $this->assertSame($this->app->site(), $blocks->parent());

        $blocks = new Blocks([], [
            'parent' => $page = new Page(['slug' => 'test'])
        ]);

        $this->assertSame($page, $blocks->parent());
    }

    public function testToArray()
    {
        $blocks = new Blocks();

        $a = new Block(['type' => 'a']);
        $b = new Block(['type' => 'b']);

        $blocks->append($a->id(), $a);
        $blocks->append($b->id(), $b);

        $expected = [
            $a->toArray(),
            $b->toArray(),
        ];

        $this->assertSame($expected, $blocks->toArray());
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
