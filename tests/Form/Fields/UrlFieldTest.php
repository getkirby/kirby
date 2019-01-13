<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class UrlFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = new Field('url');

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
        $field = new Field('url', [
            'value' => 'https://getkirby.com'
        ]);

        $this->assertTrue($field->isValid());

        $field = new Field('url', [
            'value' => 'getkirby.com'
        ]);

        $this->assertFalse($field->isValid());
    }

    public function testMinLength()
    {
        $field = new Field('url', [
            'value' => 'https://test.com',
            'minlength' => 17
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('minlength', $field->errors());
    }

    public function testMaxLength()
    {
        $field = new Field('url', [
            'value'     => 'https://test.com',
            'maxlength' => 15
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('maxlength', $field->errors());
    }
}
