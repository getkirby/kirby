<?php

namespace Kirby\Form\Fields;

class DateFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('date');

        $this->assertEquals('date', $field->type());
        $this->assertEquals('date', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->min());
        $this->assertEquals(null, $field->max());
        $this->assertEquals(false, $field->time());
        $this->assertTrue($field->save());
    }

    public function testEmptyDate()
    {
        $field = $this->field('date', [
            'value' => null
        ]);

        $this->assertSame('', $field->value());
        $this->assertEquals('', $field->toString());
    }

    public function testMinMax()
    {
        // empty
        $field = $this->field('date', [
            'min'   => '2020-10-01',
            'max'   => '2020-10-31'
        ]);

        $field->validate();
        $this->assertTrue($field->isValid());
        $this->assertFalse($field->isInvalid());

        // no limits
        $field = $this->field('date', [
            'value' => '2020-10-10'
        ]);

        $field->validate();
        $this->assertTrue($field->isValid());
        $this->assertFalse($field->isInvalid());

        // valid
        $field = $this->field('date', [
            'min'   => '2020-10-01',
            'value' => '2020-10-10',
            'max'   => '2020-10-31'
        ]);

        $field->validate();
        $this->assertTrue($field->isValid());
        $this->assertFalse($field->isInvalid());

        // same day valid
        $field = $this->field('date', [
            'min'   => '2020-10-10',
            'value' => '2020-10-10',
            'max'   => '2020-10-10'
        ]);

        $field->validate();
        $this->assertTrue($field->isValid());
        $this->assertFalse($field->isInvalid());

        // min & max failed
        $field = $this->field('date', [
            'min'   => '2020-10-01',
            'max'   => '2020-10-02',
            'value' => '2020-10-03'
        ]);

        $field->validate();
        $this->assertFalse($field->isValid());
        $this->assertTrue($field->isInvalid());

        // min failed
        $field = $this->field('date', [
            'min'   => '2020-10-01',
            'value' => '2020-09-10'
        ]);

        $field->validate();
        $this->assertFalse($field->isValid());
        $this->assertTrue($field->isInvalid());

        // max failed
        $field = $this->field('date', [
            'value' => '2020-11-10',
            'max'   => '2020-10-31'
        ]);

        $field->validate();
        $this->assertFalse($field->isValid());
        $this->assertTrue($field->isInvalid());
    }

    public function valueProvider()
    {
        return [
            ['12.12.2012', date('Y-m-d H:i:s', strtotime('2012-12-12'))],
            ['2016-11-21', date('Y-m-d H:i:s', strtotime('2016-11-21'))],
            ['2016-11-21 12:12:12', date('Y-m-d H:i:s', strtotime('2016-11-21 12:10:00')), 5],
            ['something', null],
        ];
    }

    public function testSave()
    {
        // default value
        $field = $this->field('date', [
            'value' => '12.12.2012',
        ]);

        $this->assertEquals('2012-12-12', $field->data());

        // empty value
        $field = $this->field('date', [
            'value'  => null,
        ]);

        $this->assertEquals('', $field->data());
    }

    /**
     * @link https://github.com/getkirby/kirby/issues/3642
     */
    public function testTimeWithDefaultNow()
    {
        $field = $this->field('date', [
            'time'    => true,
            'default' => 'now',
        ]);

        $now = date('Y-m-d H:i:s', strtotime('now'));
        $this->assertSame($now, $field->default());
    }

    /**
     * @dataProvider valueProvider
     */
    public function testValue($input, $expected, $step = null)
    {
        $field = $this->field('date', [
            'value' => $input,
            'time'  => ['step' => $step]
        ]);

        $this->assertEquals($expected, $field->value());
    }
}
