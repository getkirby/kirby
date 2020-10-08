<?php

namespace Kirby\Cms;

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

    public function testGroup()
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

        $grouped = $structure->group('category');

        $this->assertCount(2, $grouped);
        $this->assertCount(2, $grouped->first());
        $this->assertCount(1, $grouped->last());

        $this->assertEquals('A', $grouped->first()->first()->name());
        $this->assertEquals('C', $grouped->first()->last()->name());

        $this->assertEquals('B', $grouped->last()->first()->name());
    }

    public function testSiblings()
    {
        $structure = new Structure([
            ['name' => 'A'],
            ['name' => 'B'],
            ['name' => 'C']
        ]);


        $this->assertEquals('A', $structure->first()->name());
        $this->assertEquals('B', $structure->first()->next()->name());
        $this->assertEquals('C', $structure->last()->name());
        $this->assertEquals('B', $structure->last()->prev()->name());

        $this->assertEquals(2, $structure->last()->indexOf());

        $this->assertTrue($structure->first()->isFirst());
        $this->assertTrue($structure->last()->isLast());
        $this->assertFalse($structure->last()->isFirst());
        $this->assertFalse($structure->first()->isLast());
    }

    public function testWithInvalidData()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid structure data');

        $structure = new Structure([
            [
                'name' => 'A',
                'category' => 'cat-a'
            ],
            [
                'name' => 'B',
                'category' => 'cat-b'
            ],
            'name',
            'category'
        ]);
    }
}
