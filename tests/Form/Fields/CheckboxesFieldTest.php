<?php

namespace Kirby\Form\Fields;

class CheckboxesFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('checkboxes');

        $this->assertEquals('checkboxes', $field->type());
        $this->assertEquals('checkboxes', $field->name());
        $this->assertEquals([], $field->value());
        $this->assertEquals([], $field->options());
        $this->assertTrue($field->save());
    }

    public function testValue()
    {
        $field = $this->field('checkboxes', [
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
        $field = $this->field('checkboxes');

        $this->assertEquals([], $field->value());
    }

    public function testDefaultValueWithInvalidOptions()
    {
        $field = $this->field('checkboxes', [
            'default' => 'a,b,d',
            'options' => [
                'a',
                'b',
                'c'
            ],
        ]);

        $this->assertSame(['a', 'b'], $field->default());
        $this->assertSame('a, b', $field->data(true));

        // no default value
        $field = $this->field('checkboxes', [
            'options' => [
                'a',
                'b',
                'c'
            ],
        ]);

        $this->assertSame([], $field->default());
    }

    public function testStringConversion()
    {
        $field = $this->field('checkboxes', [
            'options' => [
                'a',
                'b',
                'c'
            ],
            'value' => 'a,b,c,d'
        ]);

        $this->assertEquals('a, b, c', $field->data());
    }

    public function testIgnoreInvalidOptions()
    {
        $field = $this->field('checkboxes', [
            'options' => [
                'a',
                'b',
                'c'
            ],
            'value' => 'a, b, d'
        ]);

        $this->assertEquals(['a', 'b'], $field->value());
    }

    public function testMin()
    {
        $field = $this->field('checkboxes', [
            'value'   => 'a',
            'options' => ['a', 'b', 'c'],
            'min'     => 2
        ]);

        $this->assertTrue($field->required());
        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('min', $field->errors());
    }

    public function testMax()
    {
        $field = $this->field('checkboxes', [
            'value'   => 'a, b',
            'options' => ['a', 'b', 'c'],
            'max'     => 1
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('max', $field->errors());
    }

    public function testRequiredProps()
    {
        $field = $this->field('checkboxes', [
            'options'  => ['a', 'b', 'c'],
            'required' => true
        ]);

        $this->assertTrue($field->required());
        $this->assertEquals(1, $field->min());
    }

    public function testRequiredInvalid()
    {
        $field = $this->field('checkboxes', [
            'options'  => ['a', 'b', 'c'],
            'value'    => null,
            'required' => true
        ]);

        $this->assertFalse($field->isValid());
    }

    public function testRequiredValid()
    {
        $field = $this->field('checkboxes', [
            'options'  => ['a', 'b', 'c'],
            'required' => true,
            'value'    => 'a'
        ]);

        $this->assertTrue($field->isValid());
    }
}
