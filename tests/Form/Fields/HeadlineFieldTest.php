<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class HeadlineFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = new Field('headline');

        $this->assertEquals('headline', $field->type());
        $this->assertEquals('headline', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->label());
        $this->assertFalse($field->save());
        $this->assertTrue($field->numbered());
    }

    public function testNumbered()
    {
        $field = new Field('headline', [
            'numbered' => true
        ]);

        $this->assertTrue($field->numbered());

        $field = new Field('headline', [
            'numbered' => false
        ]);

        $this->assertFalse($field->numbered());
    }
}
