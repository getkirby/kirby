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

    public function testHasBlockType()
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

        $this->assertTrue($layouts->hasBlockType('heading'));
        $this->assertFalse($layouts->hasBlockType('code'));
    }

    public function testParse()
    {
        $data = [
            [
                'type'    => 'heading',
                'content' => ['text' => 'Heading'],
            ],
            [
                'type'    => 'text',
                'content' => ['text' => 'Text'],
            ]
        ];
        $json = json_encode($data);

        $result = Layouts::parse($json);
        $this->assertSame($data, $result);
    }

    public function testParseArray()
    {
        $data = [
            [
                'type'    => 'heading',
                'content' => ['text' => 'Heading'],
            ],
            [
                'type'    => 'text',
                'content' => ['text' => 'Text'],
            ]
        ];

        $result = Layouts::parse($data);
        $this->assertSame($data, $result);
    }

    public function testParseEmpty()
    {
        $result = Layouts::parse(null);
        $this->assertSame([], $result);

        $result = Layouts::parse('');
        $this->assertSame([], $result);

        $result = Layouts::parse('[]');
        $this->assertSame([], $result);

        $result = Layouts::parse([]);
        $this->assertSame([], $result);

        $result = Layouts::parse('invalid json string');
        $this->assertSame([], $result);
    }

    public function testToBlocks()
    {
        $data = [
            [
                'type'    => 'heading',
                'content' => ['text' => 'Heading'],
            ],
            [
                'type'    => 'text',
                'content' => ['text' => 'Text'],
            ]
        ];

        $blocks = Layouts::factory($data)->toBlocks();

        $this->assertCount(2, $blocks);
        $this->assertInstanceOf('Kirby\Cms\Blocks', $blocks);
    }

    public function testHiddenBlocks()
    {
        $data = [
            [
                'type'     => 'heading',
                'content'  => ['text' => 'Heading'],
            ],
            [
                'type'     => 'text',
                'content'  => ['text' => 'Text'],
                'isHidden' => true,
            ]
        ];

        $layouts = Layouts::factory($data);

        $this->assertCount(1, $layouts->toBlocks());
        $this->assertCount(2, $layouts->toBlocks(true));
    }
}
