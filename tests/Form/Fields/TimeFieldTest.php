<?php

namespace Kirby\Form\Fields;

class TimeFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('time');

        $this->assertEquals('time', $field->type());
        $this->assertEquals('time', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->default());
        $this->assertEquals('clock', $field->icon());
        $this->assertEquals(24, $field->notation());
        $this->assertEquals(5, $field->step());
        $this->assertTrue($field->save());
    }

    public function valueProvider()
    {
        return [
            [null, null],
            ['invalid time', null],
            ['22:33:00', '22:33'],
            ['22:33:00', '22:30', 5],
            ['22:36:00', '22:35', 5],
            ['2012-12-12 22:33:00', '22:33']
        ];
    }

    /**
     * @dataProvider valueProvider
     */
    public function testValue($input, $expected, $step = 1)
    {
        $field = $this->field('time', [
            'default' => $input,
            'step'    => $step,
            'value'   => $input,
        ]);

        $this->assertEquals($expected, $field->value());
        $this->assertEquals($expected, $field->default());
    }
}
