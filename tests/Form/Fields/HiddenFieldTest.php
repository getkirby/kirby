<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class HiddenFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('hidden');

        $this->assertEquals('hidden', $field->type());
        $this->assertEquals('hidden', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertTrue($field->save());
    }
}
