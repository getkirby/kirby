<?php

namespace Kirby\Cms;

use Exception;

class StructureObjectTest extends TestCase
{

    public function testId()
    {
        $object = new StructureObject([
            'id' => 'test'
        ]);

        $this->assertEquals('test', $object->id());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\StructureObject::setId() must be of the type string, array given
     */
    public function testInvalidId()
    {
        $object = new StructureObject([
            'id' => []
        ]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The property "id" is required
     */
    public function testMissingId()
    {
        $object = new StructureObject(['foo' => 'bar']);
    }

    public function testCollection()
    {
        $collection = new Structure();
        $object     = new StructureObject([
            'id'         => 'test',
            'collection' => $collection
        ]);

        $this->assertEquals($collection, $object->collection());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Model::setCollection() must be an instance of Kirby\Cms\Collection or null, boolean given
     */
    public function testInvalidCollection()
    {
        $object = new StructureObject([
            'id'         => 'test',
            'collection' => false
        ]);
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

    public function testDefaultContent()
    {
        $object  = new StructureObject([
            'id' => 'test',
        ]);

        $this->assertEquals([], $object->content()->toArray());
    }

    public function testContentFields()
    {
        $object = new StructureObject([
            'id'      => 'test',
            'content' => [
                'title' => 'Title',
                'text'  => 'Text'
            ]
        ]);

        $this->assertInstanceOf(ContentField::class, $object->title());
        $this->assertInstanceOf(ContentField::class, $object->text());

        $this->assertEquals('Title', $object->title()->value());
        $this->assertEquals('Text', $object->text()->value());
    }

    public function testContentFieldsParent()
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

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\StructureObject::setParent() must be an instance of Kirby\Cms\Model or null, boolean given
     */
    public function testInvalidParent()
    {
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
