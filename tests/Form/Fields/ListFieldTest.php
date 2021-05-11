<?php

namespace Kirby\Form\Fields;

class ListFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('list');

        $this->assertSame('list', $field->type());
        $this->assertSame('list', $field->name());
        $this->assertSame('', $field->value());
        $this->assertSame(null, $field->label());
        $this->assertSame(null, $field->text());
        $this->assertTrue($field->save());
    }
}
