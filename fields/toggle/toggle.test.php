<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class ToggleFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'toggle';
    }

    public function props(): array
    {
        return [
            'name' => 'test'
        ];
    }

    public function validValueProvider()
    {
        return [
            ['true', true],
            ['1', true],
            ['on', true],
            ['false', false],
            ['0', false],
            ['off', false],
        ];
    }

    /**
     * @dataProvider validValueProvider
     */
    public function testValue($given, $expected)
    {
        $field = $this->field([
            'value' => $given
        ]);

        $this->assertEquals($expected, $field->value());
    }

    public function testSubmit()
    {
        $field = $this->field();

        $this->assertEquals('true',  $field->submit(true));
        $this->assertEquals('false', $field->submit(false));
    }

}
