<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\Page;

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
                'a',
                'b',
                'c'
            ],
            'value' => $input
        ]);

        $this->assertTrue($expected === $field->value());
    }

    public function testDefault()
    {
        $field = $this->field('radio', [
            'options' => [
                'a',
                'b',
                'c'
            ],
            'default' => '{{ page.slug }}',
            'model' => new Page([
                'slug' => 'a',
                'content' => [
                    'title' => 'A'
                ]
            ])
        ]);

        $this->assertSame('a', $field->default());
    }
}
