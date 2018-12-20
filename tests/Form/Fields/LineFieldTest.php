<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class LineFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = new Field('line');

        $this->assertEquals('line', $field->type());
        $this->assertEquals('line', $field->name());
        $this->assertFalse($field->save());
    }
}
