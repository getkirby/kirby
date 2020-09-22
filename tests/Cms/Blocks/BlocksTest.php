<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use PHPUnit\Framework\TestCase;

class BlocksTest extends TestCase
{
    protected $page;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null',
                'snippets' => __DIR__ . '/fixtures/snippets'
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
            'type'   => 'builder',
            'blocks' => [
                [
                    'content' => ['text' => 'Heading'],
                    'type'    => 'heading',
                ],
                [
                    'content' => ['text' => 'Body'],
                    'type'    => 'body',
                ],
            ]
        ]);

        $this->assertCount(2, $blocks);
        $this->assertSame($blocks, $blocks->first()->siblings());
        $this->assertEquals('Heading', $blocks->first()->text());
        $this->assertEquals('heading', $blocks->first()->type());
        $this->assertEquals('Body', $blocks->last()->text());
        $this->assertEquals('body', $blocks->last()->type());
    }

    public function testFactoryFromOldStructure()
    {
        $blocks = Blocks::factory([
            ['a' => 'Row 1 Value A', 'b' => 'Row 1 Value B'],
            ['a' => 'Row 2 Value A', 'b' => 'Row 2 Value B'],
        ]);

        $this->assertSame('structure', $blocks->type());
        $this->assertCount(2, $blocks);
        $this->assertEquals('Row 1 Value A', $blocks->first()->a());
        $this->assertEquals('Row 2 Value B', $blocks->last()->b());
    }

    public function testFactoryFromOldBuilder()
    {
        $blocks = Blocks::factory([
            [
                '_key' => 'heading',
                'text' => 'Heading'
            ],
            [
                '_key' => 'body',
                'text' => 'Body'
            ],
        ]);

        $this->assertSame('builder', $blocks->type());
        $this->assertCount(2, $blocks);
        $this->assertEquals('Heading', $blocks->first()->text());
        $this->assertEquals('heading', $blocks->first()->type());
        $this->assertEquals('Body', $blocks->last()->text());
        $this->assertEquals('body', $blocks->last()->type());
    }

    public function testFactoryFromJson()
    {
        $json   = Data::read(__DIR__ . '/fixtures/data/blocks.json');
        $blocks = Blocks::factory($json);

        $this->assertCount(2, $blocks);
        $this->assertSame($blocks, $blocks->first()->siblings());

        $this->assertSame('1234', $blocks->first()->id());
        $this->assertEquals('Heading', $blocks->first()->text());
        $this->assertEquals('Attr A', $blocks->first()->a());
        $this->assertSame('5678', $blocks->last()->id());
        $this->assertEquals('Body', $blocks->last()->text());
        $this->assertEquals('Attr A', $blocks->last()->a());
    }

    public function testFactoryFromStructureYaml()
    {
        $yaml   = Data::read(__DIR__ . '/fixtures/data/deprecated-structure.yml');
        $blocks = Blocks::factory($yaml);

        $this->assertCount(2, $blocks);
        $this->assertSame($blocks, $blocks->first()->siblings());

        $this->assertEquals('Heading', $blocks->first()->text());
        $this->assertEquals('Attr A', $blocks->first()->a());
        $this->assertEquals('Body', $blocks->last()->text());
        $this->assertEquals('Attr A', $blocks->last()->a());
    }

    public function testFactoryFromBuilderYaml()
    {
        $yaml   = Data::read(__DIR__ . '/fixtures/data/deprecated-builder.yml');
        $blocks = Blocks::factory($yaml);

        $this->assertCount(2, $blocks);
        $this->assertSame($blocks, $blocks->first()->siblings());

        $this->assertEquals('Heading', $blocks->first()->text());
        $this->assertEquals('Attr A', $blocks->first()->a());
        $this->assertEquals('Body', $blocks->last()->text());
        $this->assertEquals('Attr A', $blocks->last()->a());
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

    public function testToHtmlForStructure()
    {
        $blocks = Blocks::factory([
            [
                'text' => 'Hello world'
            ],
            [
                'text' => 'Nice blocks'
            ]
        ]);

        $expected = "<p>Hello world</p>\n<p>Nice blocks</p>\n";

        $this->assertSame($expected, $blocks->toHtml());
    }

    public function testToHtmlForBuilder()
    {
        $blocks = Blocks::factory([
            'type' => 'builder',
            'blocks' => [
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
                    'type' => 'body'
                ],
            ]
        ]);

        $expected = "<h1 class=\"builder-heading\">Hello world</h1>\n<p class=\"builder-body\">Nice blocks</p>\n";

        $this->assertSame($expected, $blocks->toHtml());
    }

    public function toHtmlWithoutSnippets()
    {
        $blocks = Blocks::factory([
            ['type' => 'a'],
            ['type' => 'b']
        ]);

        $this->assertSame('', $blocks->toHtml());
    }

    public function testType()
    {
        $blocks = new Blocks([]);

        $this->assertNull($blocks->type());

        $blocks = new Blocks([], [
            'type' => 'my-type'
        ]);

        $this->assertSame('my-type', $blocks->type());
    }

}
