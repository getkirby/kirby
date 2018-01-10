<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class TagsFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'tags';
    }

    public function testDefaultName()
    {
        $this->assertEquals('tags', $this->field()->name());
    }

    public function testDefaultLabel()
    {
        $this->assertEquals('Tags', $this->field()->label());
    }

    public function testCreateDataValue()
    {
        $field = $this->field([
            'value' => 'a, b, c'
        ]);

        $this->assertEquals(['a', 'b', 'c'], $field->value());
    }

    public function testCreateDataValueWithCustomSeparator()
    {
        $field = $this->field([
            'separator' => ';',
            'value'     => 'a; b; c'
        ]);

        $this->assertEquals(['a', 'b', 'c'], $field->value());
    }

    public function testCreateDataValueWithLowercase()
    {
        $field = $this->field([
            'lowercase' => true,
            'value'     => 'A, B, C'
        ]);

        $this->assertEquals(['a', 'b', 'c'], $field->value());
    }

    public function testCreateTextValue()
    {
        $this->assertEquals('a, b, c', $this->field()->submit(['a', 'b', 'c']));
    }

    public function testCreateTextValueWithCustomSeparator()
    {
        $this->assertEquals('a; b; c', $this->field(['separator' => ';'])->submit(['a', 'b', 'c']));
    }

    public function testCreateTextValueWithLowercase()
    {
        $this->assertEquals('a, b, c', $this->field(['lowercase' => true])->submit(['A', 'B', 'C']));
    }

}
