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
        $this->assertEquals(['size' => 5, 'unit' => 'minute'], $field->step());
        $this->assertTrue($field->save());
    }

    public function valueProvider()
    {
        return [
            [null, null],
            ['invalid time', null],
            ['22:33:00', date('Y-m-d ') . '22:33:00'],
            ['22:32:00', date('Y-m-d ') . '22:30:00', 5],
            ['22:33:00', date('Y-m-d ') . '22:35:00', 5],
            ['22:36:00', date('Y-m-d ') . '22:35:00', 5],
            ['22:39:00', date('Y-m-d ') . '22:45:00', 15],
            ['22:35:15', date('Y-m-d ') . '22:35:30', ['size'=>30, 'unit'=>'second']],
            ['22:35:15', date('Y-m-d ') . '22:00:00', ['size'=>1, 'unit'=>'hour']],
            ['2012-12-12 22:33:00', '2012-12-12 22:33:00']
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
