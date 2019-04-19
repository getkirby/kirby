<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class TextFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = new Field('text');

        $this->assertEquals('text', $field->type());
        $this->assertEquals('text', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->icon());
        $this->assertEquals(null, $field->placeholder());
        $this->assertEquals(true, $field->counter());
        $this->assertEquals(null, $field->maxlength());
        $this->assertEquals(null, $field->minlength());
        $this->assertEquals(null, $field->pattern());
        $this->assertEquals(false, $field->spellcheck());
        $this->assertTrue($field->save());
    }

    public function converterDataProvider()
    {
        return [
            ['slug', 'Super nice', 'super-nice'],
            ['upper', 'Super nice', 'SUPER NICE'],
            ['lower', 'Super nice', 'super nice'],
            ['ucfirst', 'super nice', 'Super nice']
        ];
    }

    /**
     * @dataProvider converterDataProvider
     */
    public function testConverter($converter, $input, $expected)
    {
        $field = new Field('text', [
            'converter' => $converter,
            'value'     => $input,
            'default'   => $input
        ]);

        $this->assertEquals($expected, $field->value());
        $this->assertEquals($expected, $field->default());
    }

    public function testInvalidConverter()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid converter "does-not-exist"');

        $field = new Field('text', [
            'converter' => 'does-not-exist',
        ]);
    }

    public function testMinLength()
    {
        $field = new Field('text', [
            'value' => 'test',
            'minlength' => 5
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('minlength', $field->errors());
    }

    public function testMaxLength()
    {
        $field = new Field('text', [
            'value'     => 'test',
            'maxlength' => 3
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('maxlength', $field->errors());
    }
}
