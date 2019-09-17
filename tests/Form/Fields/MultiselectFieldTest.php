<?php

namespace Kirby\Form\Fields;

class MultiselectFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('multiselect');

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

    public function testMin()
    {
        $field = $this->field('multiselect', [
            'value'   => 'a',
            'options' => ['a', 'b', 'c'],
            'min'     => 2
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('min', $field->errors());
    }

    public function testMax()
    {
        $field = $this->field('multiselect', [
            'value'   => 'a, b',
            'options' => ['a', 'b', 'c'],
            'max'     => 1
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('max', $field->errors());
    }
}
