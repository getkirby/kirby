<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class DateFieldTest extends TestCase
{

    public function testDefaultProps()
    {
        $field = new Field([
            'type' => 'date',
            'name' => 'date'
        ]);

        $this->assertEquals('date', $field->type());
        $this->assertEquals('date', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->min());
        $this->assertEquals(null, $field->max());
        $this->assertEquals(false, $field->time());
    }

    public function testEmptyDate()
    {
        $field = new Field([
            'type'  => 'date',
            'name'  => 'date',
            'value' => null
        ]);

        $this->assertNull($field->value());
        $this->assertEquals('', $field->toString());
    }


}
