<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\Page;
use Kirby\Form\Field;

class CheckboxesFieldTest extends TestCase
{

    public function testDefaultProps()
    {
        $field = new Field('checkboxes');

        $this->assertEquals('checkboxes', $field->type());
        $this->assertEquals('checkboxes', $field->name());
        $this->assertEquals([], $field->value());
        $this->assertEquals([], $field->options());
        $this->assertTrue($field->save());
    }

    public function testValue()
    {
        $field = new Field('checkboxes', [
            'value'   => 'a,b,c',
            'options' => $expected = [
                'a',
                'b',
                'c'
            ]
        ]);

        $this->assertEquals($expected, $field->value());
    }

    public function testEmptyValue()
    {
        $field = new Field('checkboxes');

        $this->assertEquals([], $field->value());
    }

    public function testDefaultValueWithInvalidOptions()
    {
        $field = new Field('checkboxes', [
            'default' => 'a,b,d',
            'options' => [
                'a',
                'b',
                'c'
            ],
        ]);

        $this->assertEquals(['a', 'b'], $field->default());
        $this->assertEquals(['a', 'b'], $field->value());
    }

    public function testStringConversion()
    {
        $field = new Field('checkboxes', [
            'options' => [
                'a',
                'b',
                'c'
            ],
            'value' => 'a,b,c,d'
        ]);

        $this->assertEquals('a, b, c', $field->toString());
    }

    public function testIgnoreInvalidOptions()
    {

        $field = new Field('checkboxes', [
            'options' => [
                'a',
                'b',
                'c'
            ],
            'value' => 'a, b, d'
        ]);

        $this->assertEquals(['a', 'b'], $field->value());

    }

}
