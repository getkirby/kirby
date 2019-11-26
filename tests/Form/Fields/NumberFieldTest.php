<?php

namespace Kirby\Form\Fields;

class NumberFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('number');

        $this->assertEquals('number', $field->type());
        $this->assertEquals('number', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->default());
        $this->assertEquals(0, $field->min());
        $this->assertEquals(null, $field->max());
        $this->assertEquals(null, $field->step());
        $this->assertTrue($field->save());
    }

    public function valueProvider()
    {
        return [
            [null, null],
            ['', null],
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
        $field = $this->field('number', [
            'value'   => $input,
            'default' => $input,
            'step'    => $input
        ]);

        $this->assertEquals($expected, $field->value());
        $this->assertEquals($expected, $field->default());

        if ($input === null) {
            $this->assertEquals(null, $field->step());
        } else {
            $this->assertEquals($expected, $field->step());
        }
    }

    public function testMin()
    {
        $field = $this->field('number', [
            'value' => 1,
            'min'   => 2
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('min', $field->errors());
    }

    public function testMax()
    {
        $field = $this->field('number', [
            'value' => 1,
            'max'   => 0
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('max', $field->errors());
    }

    public function testLargeValue()
    {
        $field = $this->field('number', [
            'value' => 1000
        ]);

        $this->assertEquals(1000, $field->value());
    }
}
