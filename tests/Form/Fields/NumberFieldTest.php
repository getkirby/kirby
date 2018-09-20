<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class NumberFieldTest extends TestCase
{

    public function testDefaultProps()
    {
        $field = new Field('number');

        $this->assertEquals('number', $field->type());
        $this->assertEquals('number', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->default());
        $this->assertEquals(0, $field->min());
        $this->assertEquals(null, $field->max());
        $this->assertEquals(1, $field->step());
        $this->assertTrue($field->save());
    }

    public function valueProvider()
    {
        return [
            [null, null],
            [false, (float)0],
            [0, (float)0],
            ['0', (float)0],
            [1, (float)1],
            ['1', (float)1],
            ['one', (float)0],
        ];
    }

    /**
     * @dataProvider valueProvider
     */
    public function testValue($input, $expected)
    {
        $field = new Field('number', [
            'value'   => $input,
            'default' => $input
        ]);

        $this->assertTrue($expected === $field->value());
        $this->assertTrue($expected === $field->default());
    }

}
