<?php

namespace Kirby\Cms;

class ContentFieldTest extends TestCase
{

    public function test__debuginfo()
    {
        $field = new ContentField('title', 'Title');
        $this->assertEquals(['title' => 'Title'], $field->__debuginfo());
    }

    public function testCall()
    {
        $this->markTestIncomplete();
    }

    public function testKey()
    {
        $field = new ContentField('title', 'Title');
        $this->assertEquals('title', $field->key());
    }

    public function testMethod()
    {
        $this->markTestIncomplete();
    }

    public function testMethods()
    {
        $this->markTestIncomplete();
    }

    public function testParent()
    {
        $parent = new Page(['id' => 'test']);
        $field  = new ContentField('title', 'Title', $parent);

        $this->assertEquals($parent, $field->parent());
    }

    public function testToString()
    {
        $field = new ContentField('title', 'Title');

        $this->assertEquals('Title', $field->toString());
        $this->assertEquals('Title', $field->__toString());
        $this->assertEquals('Title', (string)$field);
    }

    public function testToArray()
    {
        $field = new ContentField('title', 'Title');
        $this->assertEquals(['title' => 'Title'], $field->toArray());
    }

    public function testValue()
    {
        $field = new ContentField('title', 'Title');
        $this->assertEquals('Title', $field->value());
    }

    public function testValueSetter()
    {
        $field = new ContentField('title', 'Title');
        $this->assertEquals('Title', $field->value());
        $field->value('Modified');
        $this->assertEquals('Modified', $field->value());
    }

    public function testValueCallbackSetter()
    {
        $field = new ContentField('title', 'Title');
        $this->assertEquals('Title', $field->value());
        $field->value(function ($value) {
            return 'Modified';
        });
        $this->assertEquals('Modified', $field->value());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid field value type: object
     */
    public function testInvalidValueSetter()
    {
        $field = new ContentField('title', 'Title');
        $field->value(new Page(['id' => 'yay']));
    }

}
