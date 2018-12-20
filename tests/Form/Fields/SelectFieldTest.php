<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class SelectFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = new Field('select');

        $this->assertEquals('select', $field->type());
        $this->assertEquals('select', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->icon());
        $this->assertEquals([], $field->options());
        $this->assertTrue($field->save());
    }

    public function valueInputProvider()
    {
        return [
            ['a', 'a'],
            ['b', 'b'],
            ['c', 'c'],
            ['d', '']
        ];
    }

    /**
     * @dataProvider valueInputProvider
     */
    public function testValue($input, $expected)
    {
        $field = new Field('select', [
            'options' => [
                'a',
                'b',
                'c'
            ],
            'value' => $input
        ]);

        $this->assertTrue($expected === $field->value());
    }
}
