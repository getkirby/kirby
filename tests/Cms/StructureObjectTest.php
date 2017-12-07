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
     * @expectedException Exception
     * @expectedExceptionMessage The "id" attribute must be of type "string"
     */
    public function testInvalidId()
    {
        $object = new StructureObject([
            'id' => false
        ]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "id" prop is missing
     */
    public function testMissingId()
    {
        $object = new StructureObject();
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
     * @expectedException Exception
     * @expectedExceptionMessage The "collection" attribute must be of type "Kirby\Cms\Structure"
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
        $content = new Content();
        $object  = new StructureObject([
            'id'      => 'test',
            'content' => $content
        ]);

        $this->assertEquals($content, $object->content());
    }

    public function testDefaultContent()
    {
        $object  = new StructureObject([
            'id' => 'test',
        ]);

        $this->assertEquals(new Content(), $object->content());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "content" attribute must be of type "Kirby\Cms\Content"
     */
    public function testInvalidContent()
    {
        $object = new StructureObject([
            'id'      => 'test',
            'content' => false
        ]);
    }

    public function testContentFields()
    {
        $object = new StructureObject([
            'id'      => 'test',
            'content' => new Content([
                'title' => 'Title',
                'text'  => 'Text'
            ])
        ]);

        $this->assertInstanceOf(Field::class, $object->title());
        $this->assertInstanceOf(Field::class, $object->text());

        $this->assertEquals('Title', $object->title()->value());
        $this->assertEquals('Text', $object->text()->value());
    }

    public function testContentFieldsParent()
    {
        $parent = new Object();
        $object = new StructureObject([
            'id'      => 'test',
            'content' => new Content([
                'title' => 'Title',
                'text'  => 'Text'
            ], $parent)
        ]);

        $this->assertEquals($parent, $object->title()->parent());
        $this->assertEquals($parent, $object->text()->parent());
    }

    public function testParent()
    {
        $parent = new Object();
        $object = new StructureObject([
            'id'     => 'test',
            'parent' => $parent
        ]);

        $this->assertEquals($parent, $object->parent());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "parent" attribute must be of type "Kirby\Cms\Object"
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

        $object = new StructureObject([
            'id'      => 'test',
            'content' => new Content($content)
        ]);

        $this->assertEquals($content, $object->toArray());
    }

}
