<?php

namespace Kirby\Form\Fields;

class SlugFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('slug');

        $this->assertEquals('slug', $field->type());
        $this->assertEquals('slug', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals('url', $field->icon());
        $this->assertEquals('', $field->allow());
        $this->assertEquals(null, $field->path());
        $this->assertEquals(null, $field->sync());
        $this->assertEquals(null, $field->placeholder());
        $this->assertEquals(null, $field->counter());
        $this->assertEquals(false, $field->wizard());
        $this->assertTrue($field->save());
    }
}
