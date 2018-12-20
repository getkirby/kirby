<?php

namespace Kirby\Cms;

class FieldTest extends TestCase
{
    public function test__debuginfo()
    {
        $field = new Field(null, 'title', 'Title');
        $this->assertEquals(['title' => 'Title'], $field->__debuginfo());
    }

    public function testKey()
    {
        $field = new Field(null, 'title', 'Title');
        $this->assertEquals('title', $field->key());
    }

    public function testExists()
    {
        $parent = new Page([
            'slug' => 'test',
            'content' => [
                'a' => 'Value A'
            ]
        ]);

        $this->assertTrue($parent->a()->exists());
        $this->assertFalse($parent->b()->exists());
    }

    public function testParent()
    {
        $parent = new Page(['slug' => 'test']);
        $field  = new Field($parent, 'title', 'Title');

        $this->assertEquals($parent, $field->parent());
    }

    public function testToString()
    {
        $field = new Field(null, 'title', 'Title');

        $this->assertEquals('Title', $field->toString());
        $this->assertEquals('Title', $field->__toString());
        $this->assertEquals('Title', (string)$field);
    }

    public function testToArray()
    {
        $field = new Field(null, 'title', 'Title');
        $this->assertEquals(['title' => 'Title'], $field->toArray());
    }

    public function testValue()
    {
        $field = new Field(null, 'title', 'Title');
        $this->assertEquals('Title', $field->value());
    }

    public function testValueSetter()
    {
        $field = new Field(null, 'title', 'Title');
        $this->assertEquals('Title', $field->value());
        $field = $field->value('Modified');
        $this->assertEquals('Modified', $field->value());
    }

    public function testValueCallbackSetter()
    {
        $field = new Field(null, 'title', 'Title');
        $this->assertEquals('Title', $field->value());
        $field = $field->value(function ($value) {
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
        $field = new Field(null, 'title', 'Title');
        $field->value(new Page(['slug' => 'yay']));
    }

    public function testCloningInMethods()
    {
        Field::$methods = [
            'test' => function ($field) {
                $field->value = 'Test';
                return $field;
            }
        ];

        $original = new Field(null, 'title', 'Title');
        $modified = $original->test();

        $this->assertEquals('Title', $original->value);
        $this->assertEquals('Test', $modified->value);
    }
}
