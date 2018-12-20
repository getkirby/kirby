<?php

namespace Kirby\Cms;

use Exception;

class StructureTest extends TestCase
{
    public function testCreate()
    {
        $structure = new Structure([
            ['test' => 'Test']
        ]);

        $this->assertInstanceOf(StructureObject::class, $structure->first());
        $this->assertEquals('0', $structure->first()->id());
    }

    public function testParent()
    {
        $parent    = new Page(['slug' => 'test']);
        $structure = new Structure([
            ['test' => 'Test']
        ], $parent);

        $this->assertEquals($parent, $structure->first()->parent());
    }

    public function testToArray()
    {
        $data = [
            ['name' => 'A'],
            ['name' => 'B']
        ];

        $expected = [
            ['id' => 0, 'name' => 'A'],
            ['id' => 1, 'name' => 'B'],
        ];

        $structure = new Structure($data);

        $this->assertEquals($expected, $structure->toArray());
    }

    public function testGroupBy()
    {
        $structure = new Structure([
            [
                'name' => 'A',
                'category' => 'cat-a'
            ],
            [
                'name' => 'B',
                'category' => 'cat-b'
            ],
            [
                'name' => 'C',
                'category' => 'cat-a'
            ]
        ]);

        $grouped = $structure->groupBy('category');

        $this->assertCount(2, $grouped);
        $this->assertCount(2, $grouped->first());
        $this->assertCount(1, $grouped->last());

        $this->assertEquals('A', $grouped->first()->first()->name());
        $this->assertEquals('C', $grouped->first()->last()->name());

        $this->assertEquals('B', $grouped->last()->first()->name());
    }
}
