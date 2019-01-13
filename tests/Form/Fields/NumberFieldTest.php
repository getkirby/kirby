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
            ['1.1', (float)1.1],
            ['1.11111', (float)1.11111],
            [1.11111, (float)1.11111],
            ['1,1', (float)1.1],
        ];
    }

    /**
     * @dataProvider valueProvider
     */
    public function testValue($input, $expected)
    {
        $field = new Field('number', [
            'value'   => $input,
            'default' => $input,
            'step'    => $input
        ]);

        $this->assertTrue($expected === $field->value());
        $this->assertTrue($expected === $field->default());

        if ($input === null) {
            $this->assertTrue((float)1 === $field->step());
        } else {
            $this->assertTrue($expected === $field->step());
        }
    }

    public function testMin()
    {
        $field = new Field('number', [
            'value' => 1,
            'min'   => 2
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('min', $field->errors());
    }

    public function testMax()
    {
        $field = new Field('number', [
            'value' => 1,
            'max'   => 0
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('max', $field->errors());
    }
}
