<?php

namespace Kirby\Form\Fields;

class TelFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('tel');

        $this->assertEquals('tel', $field->type());
        $this->assertEquals('tel', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals('phone', $field->icon());
        $this->assertEquals(null, $field->placeholder());
        $this->assertEquals(null, $field->counter());
        $this->assertEquals('tel', $field->autocomplete());
        $this->assertTrue($field->save());
    }
}
