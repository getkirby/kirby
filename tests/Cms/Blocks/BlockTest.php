<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class BlockTest extends TestCase
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

    public function testAttrs()
    {
        $block = new Block([
            'type' => 'heading',
            'attrs' => $attrs = [
                'a' => 'Test Attr A',
                'b' => 'Test Attr B'
            ]
        ]);

        $this->assertEquals('Test Attr A', $block->attrs()->a());
        $this->assertEquals('Test Attr A', $block->attr('a'));
        $this->assertEquals('Test Attr A', $block->a());
        $this->assertEquals('Test Attr B', $block->attrs()->b());
        $this->assertEquals('Test Attr B', $block->attr('b'));
        $this->assertEquals('Test Attr B', $block->b());
        $this->assertSame($attrs, $block->attrs()->toArray());

        // attr with fallback
        $this->assertEquals('Test Attr C', $block->attr('c', 'Test Attr C'));
    }

    public function testAttrsAccess()
    {
        $block = new Block([
            'type' => 'heading',
            'attrs' => $attrs = [
                'a' => 'Test Attr A',
                'b' => 'Test Attr B'
            ],
            'content' => [
                'a' => 'Test Content A',
                'b' => 'Test Content B'
            ]
        ]);

        $this->assertEquals('Test Attr A', $block->attrs()->a());
        $this->assertEquals('Test Content A', $block->a());
        $this->assertEquals('Test Attr B', $block->attrs()->b());
        $this->assertEquals('Test Content B', $block->b());
    }

    public function testConstruct()
    {
        $block = new Block(['type' => 'test']);

        $this->assertInstanceOf('Kirby\Cms\Content', $block->attrs());
        $this->assertInstanceOf('Kirby\Cms\Content', $block->content());
        $this->assertNotNull($block->id());
        $this->assertSame($this->app, $block->kirby());
        $this->assertInstanceOf('Kirby\Cms\Site', $block->parent());
        $this->assertInstanceOf('Kirby\Cms\Blocks', $block->siblings());
        $this->assertSame('blocks/test', $block->snippet());
        $this->assertSame('test', $block->type());
    }

    public function testConstructWithoutType()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The block type is missing');

        $block = new Block([]);
    }

    public function testContent()
    {
        $block = new Block([
            'type'    => 'heading',
            'content' => $content = [
                'a' => 'Test Field A',
                'b' => 'Test Field B'
            ]
        ]);

        $this->assertEquals('Test Field A', $block->content()->a());
        $this->assertEquals('Test Field A', $block->a());
        $this->assertEquals('Test Field B', $block->content()->b());
        $this->assertEquals('Test Field B', $block->b());
        $this->assertSame($content, $block->content()->toArray());
    }

    public function testController()
    {
        $block = new Block([
            'type' => 'heading',
            'attrs' => [
                'a' => 'Test Attr A',
                'b' => 'Test Attr B'
            ],
            'content' => [
                'a' => 'Test Content A',
                'b' => 'Test Content B'
            ]
        ]);

        $this->assertSame($block->attrs(), $block->controller()['attrs']);
        $this->assertSame($block->content(), $block->controller()['content']);
        $this->assertSame($block, $block->controller()['block']);
        $this->assertSame($block->id(), $block->controller()['id']);
        $this->assertNull($block->controller()['prev']);
        $this->assertNull($block->controller()['next']);
    }

    public function testFactory()
    {
        $block = Block::factory([
            'type' => 'heading'
        ]);

        $this->assertInstanceOf('Kirby\Cms\Block', $block);
    }

    public function testIs()
    {
        $a = new Block(['type' => 'a']);
        $b = new Block(['type' => 'b']);

        $this->assertTrue($a->is($a));
        $this->assertFalse($a->is($b));
    }

    public function testIsEmpty()
    {
        $block = new Block([
            'type' => 'heading'
        ]);

        $this->assertTrue($block->isEmpty());
        $this->assertFalse($block->isNotEmpty());

        $block = new Block([
            'type' => 'heading',
            'content' => [
                'text' => 'This is a nice heading'
            ]
        ]);

        $this->assertFalse($block->isEmpty());
        $this->assertTrue($block->isNotEmpty());
    }

    public function testParent()
    {
        $block = new Block([
            'parent' => $page = new Page(['slug' => 'test']),
            'type'   => 'heading'
        ]);

        $this->assertSame($page, $block->parent());
        $this->assertSame($page, $block->attrs()->parent());
        $this->assertSame($page, $block->content()->parent());
    }

    public function testSiblings()
    {
        $blocks = Blocks::factory([
            ['type' => 'a'],
            ['type' => 'b'],
        ]);

        $block = new Block([
            'siblings' => $blocks,
            'type'     => 'c'
        ]);

        $this->assertSame($blocks, $block->siblings());
    }

    public function testSnippet()
    {
        // without parent field type
        $block = new Block([
            'type' => 'heading',
        ]);

        $this->assertSame('blocks/heading', $block->snippet());

        // with parent field type
        $block = new Block([
            'type'  => 'heading',
            'field' => 'builder',
        ]);

        $this->assertSame('builder/heading', $block->snippet());
    }

    public function testToArray()
    {
        $block = new Block([
            'type' => 'heading',
            'attrs' => $attrs = [
                'a' => 'Test Attr A',
                'b' => 'Test Attr B'
            ],
            'content' => $content = [
                'a' => 'Test Content A',
                'b' => 'Test Content B'
            ]
        ]);

        $this->assertSame([
            'attrs'   => $attrs,
            'content' => $content,
            'id'      => $block->id(),
            'type'    => 'heading'
        ], $block->toArray());
    }

    public function testToField()
    {
        $block = new Block([
            'content' => [
                'text' => 'Test'
            ],
            'type' => 'heading',
        ]);

        $expected = "<h1>Test</h1>\n";

        $this->assertInstanceOf('Kirby\Cms\Field', $block->toField());
        $this->assertSame($block->parent(), $block->toField()->parent());
        $this->assertSame($block->id(), $block->toField()->key());
        $this->assertSame($expected, $block->toField()->value());
    }

    public function testToHtml()
    {
        $block = new Block([
            'content' => [
                'text' => 'Test'
            ],
            'type' => 'heading',
        ]);

        $expected = "<h1>Test</h1>\n";

        $this->assertSame($expected, $block->toHtml());
        $this->assertSame($expected, $block->__toString());
        $this->assertSame($expected, (string)$block);
    }

    public function testToHtmlWithoutSnippet()
    {
        $block = new Block([
            'content' => [
                'text' => 'Test'
            ],
            'type' => 'does-not-exist',
        ]);

        $expected = '';

        $this->assertSame($expected, $block->toHtml());
        $this->assertSame($expected, $block->__toString());
        $this->assertSame($expected, (string)$block);
    }
}
