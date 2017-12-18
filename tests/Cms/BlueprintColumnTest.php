<?php

namespace Kirby\Cms;

class BlueprintColumnTest extends TestCase
{

    public function sections()
    {
        return [
            [
                'name' => 'pages',
                'type' => 'pages'
            ],
            [
                'name' => 'files',
                'type' => 'files'
            ]
        ];
    }

    public function testSections()
    {
        $column = new BlueprintColumn([
            'sections' => $this->sections()
        ]);

        $this->assertInstanceOf(Collection::class, $column->sections());
        $this->assertCount(2, $column->sections());
        $this->assertEquals('pages', $column->sections()->first()->type());
        $this->assertEquals('files', $column->sections()->last()->type());
    }

    public function testSection()
    {
        $column = new BlueprintColumn([
            'sections' => $this->sections()
        ]);

        $this->assertInstanceOf(BlueprintSection::class, $column->section('pages'));
    }

    public function testMissingSection()
    {
        $column = new BlueprintColumn(['sections' => []]);
        $this->assertNull($column->section('pages'));
    }

    public function widthProvider()
    {
        return [
            ['1/1'],
            ['1/2'],
            ['1/3'],
            ['2/3']
        ];
    }

    /**
     * @dataProvider widthProvider
     */
    public function testWidth($width)
    {
        $column = new BlueprintColumn([
            'sections' => [],
            'width'    => $width
        ]);

        $this->assertEquals($width, $column->width());
    }

    public function testDefaultWidth()
    {
        $column = new BlueprintColumn([
            'sections' => []
        ]);

        $this->assertEquals('1/1', $column->width());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Prop validation for "width" failed
     */
    public function testInvalidWidth()
    {
        $column = new BlueprintColumn([
            'sections' => [],
            'width'    => '1/4'
        ]);
    }

    public function testToArrayWithDefaults()
    {
        $column = new BlueprintColumn([
            'sections' => []
        ]);

        $expected = [
            'sections' => [],
            'width'    => '1/1'
        ];

        $this->assertEquals($expected, $column->toArray());
    }

    public function testToArray()
    {
        $column = new BlueprintColumn([
            'sections' => $this->sections(),
            'width'    => '1/2'
        ]);

        $expected = [
            'sections' => [
                [
                    'fields' => [],
                    'id'     => 'pages',
                    'name'   => 'pages',
                    'type'   => 'pages',
                ],
                [
                    'fields' => [],
                    'id'     => 'files',
                    'name'   => 'files',
                    'type'   => 'files',
                ]
            ],
            'width'    => '1/2'
        ];

        $this->assertEquals($expected, $column->toArray());
    }

    public function testToLayoutWithDefaults()
    {
        $column = new BlueprintColumn([
            'sections' => []
        ]);

        $expected = [
            'sections' => [],
            'width'    => '1/1'
        ];

        $this->assertEquals($expected, $column->toLayout());
    }

    public function testToLayout()
    {
        $column = new BlueprintColumn([
            'sections' => $this->sections()
        ]);

        $expected = [
            'sections' => $column->sections()->keys(),
            'width'    => '1/1'
        ];

        $this->assertEquals($expected, $column->toLayout());
    }

}
