<?php

namespace Kirby\Form\Fields;

class LineFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('line');

        $this->assertEquals('line', $field->type());
        $this->assertEquals('line', $field->name());
        $this->assertFalse($field->save());
    }
}
