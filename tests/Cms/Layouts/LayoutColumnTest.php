<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class LayoutColumnTest extends TestCase
{
    public function testConstruct()
    {
        $column = new LayoutColumn();
        $this->assertInstanceOf('Kirby\Cms\Blocks', $column->blocks());
        $this->assertSame('1/1', $column->width());
        $this->assertSame(12, $column->span());
    }

    public function testBlocks()
    {
        $column = new LayoutColumn([
            'blocks' => [
                ['type' => 'heading'],
                ['type' => 'text'],
            ]
        ]);
        $this->assertCount(2, $column->blocks());
        $this->assertSame('heading', $column->blocks()->first()->type());
        $this->assertSame('text', $column->blocks()->last()->type());
    }

    public function testSpan()
    {
        $column = new LayoutColumn([
            'width' => '1/2'
        ]);

        $this->assertSame(6, $column->span());
        $this->assertSame(3, $column->span(6));
    }

    public function testWidth()
    {
        $column = new LayoutColumn([
            'width' => '1/2'
        ]);

        $this->assertSame('1/2', $column->width());
    }
}
