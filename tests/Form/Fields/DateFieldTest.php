<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class DateFieldTest extends TestCase
{

    public function testDefaultProps()
    {
        $field = new Field('date');

        $this->assertEquals('date', $field->type());
        $this->assertEquals('date', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->min());
        $this->assertEquals(null, $field->max());
        $this->assertEquals(false, $field->time());
        $this->assertTrue($field->save());
    }

    public function testEmptyDate()
    {
        $field = new Field('date', [
            'value' => null
        ]);

        $this->assertNull($field->value());
        $this->assertEquals('', $field->toString());
    }

}
