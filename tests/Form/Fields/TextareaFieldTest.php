<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class TextareaFieldTest extends TestCase
{

    public function testDefaultProps()
    {
        $field = new Field('textarea');

        $this->assertEquals('textarea', $field->type());
        $this->assertEquals('textarea', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->icon());
        $this->assertEquals(null, $field->placeholder());
        $this->assertEquals(true, $field->counter());
        $this->assertEquals(null, $field->maxlength());
        $this->assertEquals(null, $field->minlength());
        $this->assertEquals(null, $field->size());
        $this->assertTrue($field->save());
    }

}
