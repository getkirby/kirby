<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class MultiselectFieldTest extends TestCase
{

    public function testDefaultProps()
    {
        $field = new Field('multiselect');

        $this->assertEquals('multiselect', $field->type());
        $this->assertEquals('multiselect', $field->name());
        $this->assertEquals([], $field->value());
        $this->assertEquals([], $field->default());
        $this->assertEquals([], $field->options());
        $this->assertEquals(null, $field->min());
        $this->assertEquals(null, $field->max());
        $this->assertEquals(',', $field->separator());
        $this->assertEquals(null, $field->icon());
        $this->assertEquals(null, $field->counter());
        $this->assertTrue($field->search());
        $this->assertFalse($field->sort());
        $this->assertTrue($field->save());
    }

}
