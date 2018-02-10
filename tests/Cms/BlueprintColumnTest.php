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
            'name'     => 'test',
            'sections' => $this->sections(),
            'model'    => new Page(['slug' => 'test'])
        ]);

        $this->assertInstanceOf(Collection::class, $column->sections());
        $this->assertCount(2, $column->sections());
        $this->assertEquals('pages', $column->sections()->first()->type());
        $this->assertEquals('files', $column->sections()->last()->type());
    }

    public function testSection()
    {
        $column = new BlueprintColumn([
            'name'     => 'test',
            'sections' => $this->sections(),
            'model'    => new Page(['slug' => 'test'])
        ]);

        $this->assertInstanceOf(BlueprintSection::class, $column->section('pages'));
    }

    public function testMissingSection()
    {
        $column = new BlueprintColumn([
            'name'     => 'test',
            'sections' => [],
            'model'    => new Page(['slug' => 'test'])
        ]);
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
            'name'     => 'test',
            'sections' => [],
            'width'    => $width,
            'model'    => new Page(['slug' => 'test'])
        ]);

        $this->assertEquals($width, $column->width());
    }

    public function testDefaultWidth()
    {
        $column = new BlueprintColumn([
            'name'     => 'test',
            'sections' => [],
            'model'    => new Page(['slug' => 'test'])
        ]);

        $this->assertEquals('1/1', $column->width());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid width value
     */
    public function testInvalidWidth()
    {
        $column = new BlueprintColumn([
            'name'     => 'test',
            'sections' => [],
            'width'    => '1/4',
            'model'    => new Page(['slug' => 'test'])
        ]);
    }

    public function testToArrayWithDefaults()
    {
        $column = new BlueprintColumn([
            'name'     => 'test',
            'sections' => [],
            'model'    => new Page(['slug' => 'test'])
        ]);

        $expected = [
            'name'     => 'test',
            'sections' => [],
            'width'    => '1/1',
            'id'       => 'test',
        ];

        $this->assertEquals($expected, $column->toArray());
    }


    public function testToLayoutWithDefaults()
    {
        $column = new BlueprintColumn([
            'name'     => 'test',
            'sections' => [],
            'model'    => new Page(['slug' => 'test'])
        ]);

        $expected = [
            'name'     => 'test',
            'sections' => [],
            'width'    => '1/1',
            'id'       => 'test',
        ];

        $this->assertEquals($expected, $column->toLayout());
    }

    public function testToLayout()
    {
        $column = new BlueprintColumn([
            'name'     => 'test',
            'sections' => $this->sections(),
            'model'    => new Page(['slug' => 'test'])
        ]);

        $expected = [
            'name'     => 'test',
            'sections' => $column->sections()->keys(),
            'width'    => '1/1',
            'id'       => 'test',
        ];

        $this->assertEquals($expected, $column->toLayout());
    }

}
