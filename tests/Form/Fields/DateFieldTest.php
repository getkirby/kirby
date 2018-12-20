<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class DateFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = new Field('date');

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
        $field = new Field('date', [
            'value' => null
        ]);

        $this->assertNull($field->value());
        $this->assertEquals('', $field->toString());
    }

    public function valueProvider()
    {
        return [
            ['12.12.2012', date(DATE_W3C, strtotime('2012-12-12'))],
            ['2016-11-21', date(DATE_W3C, strtotime('2016-11-21'))],
            ['something', null],
        ];
    }

    /**
     * @dataProvider valueProvider
     */
    public function testValue($input, $expected)
    {
        $field = new Field('date', [
            'value' => $input
        ]);

        $this->assertEquals($expected, $field->value());
    }
}
