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
        $this->assertEquals('HH:mm', $field->display());
        $this->assertEquals('clock', $field->icon());
        $this->assertEquals(24, $field->notation());
        $this->assertEquals(['size' => 5, 'unit' => 'minute'], $field->step());
        $this->assertTrue($field->save());
    }

    public function testDisplayFor12HourNotation()
    {
        $field = $this->field('time', [
            'notation' => 12
        ]);

        $this->assertSame('hh:mm a', $field->display());
    }

    public function testDisplayWithCustomSetup()
    {
        $field = $this->field('time', [
            'display' => 'HH:mm:ss'
        ]);

        $this->assertSame('HH:mm:ss', $field->display());
    }

    public function testMinMax()
    {
        // no value
        $field = $this->field('time', [
            'value' => null
        ]);

        $field->validate();
        $this->assertTrue($field->isValid());
        $this->assertFalse($field->isInvalid());

        // no limits
        $field = $this->field('time', [
            'value' => '10:00:00'
        ]);

        $field->validate();
        $this->assertTrue($field->isValid());
        $this->assertFalse($field->isInvalid());

        // valid
        $field = $this->field('time', [
            'min'   => '09:00',
            'value' => '10:00:00',
            'max'   => '11:00'
        ]);

        $field->validate();
        $this->assertTrue($field->isValid());
        $this->assertFalse($field->isInvalid());

        // same time valid
        $field = $this->field('time', [
            'min'   => '10:00',
            'value' => '10:00:00',
            'max'   => '10:00'
        ]);

        $field->validate();
        $this->assertTrue($field->isValid());
        $this->assertFalse($field->isInvalid());

        // min & max failed
        $field = $this->field('time', [
            'value' => '10:00:00',
            'min'   => '08:00',
            'max'   => '09:00'
        ]);

        $field->validate();
        $this->assertFalse($field->isValid());
        $this->assertTrue($field->isInvalid());

        // min failed
        $field = $this->field('time', [
            'min'   => '11:00',
            'value' => '10:00:00'
        ]);

        $field->validate();
        $this->assertFalse($field->isValid());
        $this->assertTrue($field->isInvalid());

        // max failed
        $field = $this->field('time', [
            'value' => '10:00:00',
            'max'   => '09:00'
        ]);

        $field->validate();
        $this->assertFalse($field->isValid());
        $this->assertTrue($field->isInvalid());

        // valid with seconds
        $field = $this->field('time', [
            'min'   => '09:00:00',
            'value' => '10:00:00',
            'max'   => '11:00:00',
            'step'  => 'second'
        ]);

        $field->validate();
        $this->assertTrue($field->isValid());
        $this->assertFalse($field->isInvalid());

        // invalid with seconds
        $field = $this->field('time', [
            'min'   => '10:00:05',
            'value' => '10:00:00',
            'step'  => 'second'
        ]);

        $field->validate();
        $this->assertFalse($field->isValid());
        $this->assertTrue($field->isInvalid());

        $field = $this->field('time', [
            'value' => '10:00:05',
            'max'   => '10:00:00',
            'step'  => 'second'
        ]);

        $field->validate();
        $this->assertFalse($field->isValid());
        $this->assertTrue($field->isInvalid());
    }

    public function valueProvider()
    {
        return [
            [null, ''],
            ['invalid time', ''],
            ['22:33:00', '22:33:00'],
            ['22:32:00', '22:30:00', 5],
            ['22:33:00', '22:35:00', 5],
            ['22:36:00', '22:35:00', 5],
            ['22:39:00', '22:45:00', 15],
            ['22:35:15', '22:35:30', ['size' => 30, 'unit' => 'second']],
            ['22:35:15', '23:00:00', ['size' => 1, 'unit' => 'hour']],
            ['2012-12-12 22:33:00', '22:33:00']
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

        $this->assertSame($expected, $field->value());
        $this->assertSame($expected, $field->default());
    }
}
