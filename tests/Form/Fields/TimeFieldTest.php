<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class TimeFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = new Field('time');

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
            ['2012-12-12 22:33:00', '22:33']
        ];
    }

    /**
     * @dataProvider valueProvider
     */
    public function testValue($input, $expected)
    {
        $field = new Field('time', [
            'value'   => $input,
            'default' => $input
        ]);

        $this->assertEquals($expected, $field->value());
        $this->assertEquals($expected, $field->default());
    }
}
