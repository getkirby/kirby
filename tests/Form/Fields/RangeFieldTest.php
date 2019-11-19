<?php

namespace Kirby\Form\Fields;

class RangeFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('range');

        $this->assertEquals('range', $field->type());
        $this->assertEquals('range', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->default());
        $this->assertEquals(0, $field->min());
        $this->assertEquals(100, $field->max());
        $this->assertEquals(null, $field->step());
        $this->assertTrue($field->tooltip());
        $this->assertTrue($field->save());
    }

    public function testMin()
    {
        $field = $this->field('range', [
            'value' => 1,
            'min'   => 2
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('min', $field->errors());
    }

    public function testMax()
    {
        $field = $this->field('range', [
            'value' => 1,
            'max'   => 0
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('max', $field->errors());
    }
}
