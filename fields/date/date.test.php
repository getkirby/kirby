<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class DateFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'date';
    }

    public function testDefaultName()
    {
        $this->assertEquals('date', $this->field()->name());
    }

    public function testDefaultLabel()
    {
        $this->assertEquals('Date', $this->field()->label());
    }

    public function testDefaultIcon()
    {
        $this->assertEquals('calendar', $this->field()->icon());
    }

    public function validDataValueProvider()
    {
        return [
            ['2012-12-12', '2012-12-12T00:00:00+01:00'],
            ['12.12.2012', '2012-12-12T00:00:00+01:00'],
            ['12. December 2012', '2012-12-12T00:00:00+01:00']
        ];
    }

    /**
     * @dataProvider validDataValueProvider
     */
    public function testCreateDataValue($date, $expected)
    {
        $field = $this->field([
            'value' => $date
        ]);

        $this->assertEquals($expected, $field->value());
    }

    public function validTextValueProvider()
    {
        return [
            ['2012-12-12T00:00:00+01:00', '2012-12-12T00:00:00+01:00'],
            ['12.12.2012', '2012-12-12T00:00:00+01:00'],
            ['12. December 2012', '2012-12-12T00:00:00+01:00']
        ];
    }

    /**
     * @dataProvider validTextValueProvider
     */
    public function testCreateTextValue($input, $expected)
    {
        $this->assertEquals($expected, $this->field()->submit($input));
    }

    public function testCreateTextValueWithCustomFormat()
    {
        $field = $this->field([
            'format' => 'd.m.Y'
        ]);

        $this->assertEquals('12.12.2012', $field->submit('2012-12-12'));
    }

}
