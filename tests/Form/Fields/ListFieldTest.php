<?php

namespace Kirby\Form\Fields;

class ListFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('list');

        $this->assertSame('list', $field->type());
        $this->assertSame('list', $field->name());
        $this->assertSame(null, $field->value());
        $this->assertSame(null, $field->label());
        $this->assertSame(null, $field->text());
        $this->assertTrue($field->save());
    }

    public function testSave()
    {
        // default value
        $field = $this->field('list', [
            'value' => $value = '<ul><li>List Item A</li><li>List Item B</li><li>List Item C</li></ul>'
        ]);

        $this->assertSame($value, $field->data());

        // with html value
        $value    = '<ul><li><p>List Item A</p></li><li><p><u>List Item B</u></p></li><li><p><em>List Item C</em></p></li></ul>';
        $expected = '<ul><li>List Item A</li><li><u>List Item B</u></li><li><em>List Item C</em></li></ul>';

        $field = $this->field('list', [
            'value' => $value
        ]);

        $this->assertSame($expected, $field->data());

        // empty value
        $field = $this->field('list', [
            'value' => ''
        ]);

        $this->assertSame('', $field->data());

        // null value
        $field = $this->field('list', [
            'value' => ''
        ]);

        $this->assertSame('', $field->data());
    }
}
