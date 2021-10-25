<?php

namespace Kirby\Form\Fields;

class GapFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('gap');

        $this->assertEquals('gap', $field->type());
        $this->assertEquals('gap', $field->name());
        $this->assertFalse($field->save());
    }
}
