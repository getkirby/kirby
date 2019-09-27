<?php

namespace Kirby\Form\Fields;

class UrlFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('url');

        $this->assertEquals('url', $field->type());
        $this->assertEquals('url', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals('url', $field->icon());
        $this->assertEquals('https://example.com', $field->placeholder());
        $this->assertEquals(null, $field->counter());
        $this->assertEquals('url', $field->autocomplete());
        $this->assertTrue($field->save());
    }

    public function testUrlValidation()
    {
        $field = $this->field('url', [
            'value' => 'https://getkirby.com'
        ]);

        $this->assertTrue($field->isValid());

        $field = $this->field('url', [
            'value' => 'getkirby.com'
        ]);

        $this->assertFalse($field->isValid());
    }

    public function testMinLength()
    {
        $field = $this->field('url', [
            'value' => 'https://test.com',
            'minlength' => 17
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('minlength', $field->errors());
    }

    public function testMaxLength()
    {
        $field = $this->field('url', [
            'value'     => 'https://test.com',
            'maxlength' => 15
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('maxlength', $field->errors());
    }
}
