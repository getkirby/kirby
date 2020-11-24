<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class LayoutsTest extends TestCase
{
    public function testFactory()
    {
        $layouts = Layouts::factory([
            [
                'columns' => [
                    [
                        'width' => '1/2'
                    ],
                    [
                        'width' => '1/2'
                    ]
                ]
            ]
        ]);

        $this->assertInstanceOf('Kirby\Cms\Layout', $layouts->first());
        $this->assertSame('1/2', $layouts->first()->columns()->first()->width());
    }

    public function testFactoryIsWrappingBlocks()
    {
        $layouts = Layouts::factory([
            [
                'type'    => 'heading',
                'content' => ['text' => 'Heading'],
            ],
            [
                'type'    => 'text',
                'content' => ['text' => 'Text'],
            ]
        ]);

        $this->assertInstanceOf('Kirby\Cms\Layout', $layouts->first());

        $columns = $layouts->first()->columns();
        $blocks  = $columns->first()->blocks();

        $this->assertEquals('heading', $blocks->first()->type());
        $this->assertEquals('Heading', $blocks->first()->text());
        $this->assertEquals('text', $blocks->last()->type());
        $this->assertEquals('Text', $blocks->last()->text());
    }
}
