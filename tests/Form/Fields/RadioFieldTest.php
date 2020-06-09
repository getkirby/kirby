<?php

namespace Kirby\Form\Fields;

class RadioFieldTest extends TestCase
{

    public function testDefaultProps()
    {
        $field = $this->field('radio');

        $this->assertEquals('radio', $field->type());
        $this->assertEquals('radio', $field->name());
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
        $field = $this->field('radio', [
            'options' => [
                ['value' => 'a', 'text' => 'a'],
                ['value' => 'b', 'text' => 'b'],
                ['value' => 'c', 'text' => 'c']
            ],
            'value' => $input
        ]);

        $this->assertSame($expected, $field->value());
    }

    public function testDefaultValue()
    {
        $field = $this->field('radio', [
            'default' => 'c',
            'options' => [
                ['value' => 'a', 'text' => 'a'],
                ['value' => 'b', 'text' => 'b'],
                ['value' => 'c', 'text' => 'c']
            ],
        ]);

        $this->assertSame('c', $field->default());
        $this->assertSame('c', $field->data(true));
    }

    public function testDefaultValueWithInvalidOptions()
    {
        $field = $this->field('radio', [
            'default' => 'd',
            'options' => [
                ['value' => 'a', 'text' => 'a'],
                ['value' => 'b', 'text' => 'b'],
                ['value' => 'c', 'text' => 'c']
            ],
        ]);

        $this->assertNull($field->default());
        $this->assertNull($field->data(true));
    }
}
