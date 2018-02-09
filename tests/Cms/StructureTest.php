<?php

namespace Kirby\Cms;

use Exception;

class StructureTest extends TestCase
{

    public function testObject()
    {
        $structure = new Structure([
            new StructureObject([
                'id' => 'test'
            ])
        ]);

        $this->assertInstanceOf(StructureObject::class, $structure->first());
        $this->assertEquals('test', $structure->first()->id());
    }

    public function testArray()
    {
        $structure = new Structure([
            ['test' => 'Test']
        ]);

        $this->assertInstanceOf(StructureObject::class, $structure->first());
        $this->assertEquals('0', $structure->first()->id());
    }

    public function testParent()
    {
        $parent    = new Page(['id' => 'test']);
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

}
