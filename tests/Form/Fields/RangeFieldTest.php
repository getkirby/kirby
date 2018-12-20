<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class RangeFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = new Field('range');

        $this->assertEquals('range', $field->type());
        $this->assertEquals('range', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->default());
        $this->assertEquals(0, $field->min());
        $this->assertEquals(100, $field->max());
        $this->assertEquals(1, $field->step());
        $this->assertTrue($field->tooltip());
        $this->assertTrue($field->save());
    }

    public function testMin()
    {
        $field = new Field('range', [
            'value' => 1,
            'min'   => 2
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('min', $field->errors());
    }

    public function testMax()
    {
        $field = new Field('range', [
            'value' => 1,
            'max'   => 0
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('max', $field->errors());
    }
}
