<?php

namespace Kirby\Cms;

class StructureObjectTest extends TestCase
{
    public function testId()
    {
        $object = new StructureObject([
            'id' => 'test'
        ]);

        $this->assertEquals('test', $object->id());
    }

    public function testInvalidId()
    {
        $this->expectException('TypeError');

        $object = new StructureObject([
            'id' => []
        ]);
    }

    public function testMissingId()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The property "id" is required');

        $object = new StructureObject(['foo' => 'bar']);
    }

    public function testContent()
    {
        $content = ['test' => 'Test'];
        $object  = new StructureObject([
            'id'      => 'test',
            'content' => $content
        ]);

        $this->assertEquals($content, $object->content()->toArray());
    }

    public function testToDate()
    {
        $object = new StructureObject([
            'id'      => 'test',
            'content' => [
                'date' => '2012-12-12'
            ]
        ]);

        $this->assertEquals('12.12.2012', $object->date()->toDate('d.m.Y'));
    }

    public function testDefaultContent()
    {
        $object  = new StructureObject([
            'id' => 'test',
        ]);

        $this->assertEquals([], $object->content()->toArray());
    }

    public function testFields()
    {
        $object = new StructureObject([
            'id'      => 'test',
            'content' => [
                'title' => 'Title',
                'text'  => 'Text'
            ]
        ]);

        $this->assertInstanceOf(Field::class, $object->title());
        $this->assertInstanceOf(Field::class, $object->text());

        $this->assertEquals('Title', $object->title()->value());
        $this->assertEquals('Text', $object->text()->value());
    }

    public function testFieldsParent()
    {
        $parent = new Page(['slug' => 'test']);
        $object = new StructureObject([
            'id'      => 'test',
            'content' => [
                'title' => 'Title',
                'text'  => 'Text'
            ],
            'parent' => $parent
        ]);

        $this->assertEquals($parent, $object->title()->parent());
        $this->assertEquals($parent, $object->text()->parent());
    }

    public function testParent()
    {
        $parent = new Page(['slug' => 'test']);
        $object = new StructureObject([
            'id'     => 'test',
            'parent' => $parent
        ]);

        $this->assertEquals($parent, $object->parent());
    }

    public function testInvalidParent()
    {
        $this->expectException('TypeError');

        $object = new StructureObject([
            'id'     => 'test',
            'parent' => false
        ]);
    }

    public function testToArray()
    {
        $content = [
            'title' => 'Title',
            'text'  => 'Text'
        ];

        $expected = [
            'id'    => 'test',
            'text'  => 'Text',
            'title' => 'Title',
        ];

        $object = new StructureObject([
            'id'      => 'test',
            'content' => $content
        ]);

        $this->assertEquals($expected, $object->toArray());
    }
}
