<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\Page;
use Kirby\Form\Field;

class InfoFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = new Field('info');

        $this->assertEquals('info', $field->type());
        $this->assertEquals('info', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->label());
        $this->assertEquals(null, $field->text());
        $this->assertFalse($field->save());
    }

    public function testText()
    {

        // simple text
        $field = new Field('info', [
            'text' => 'test'
        ]);

        $this->assertEquals('<p>test</p>', $field->text());

        // translated text
        $field = new Field('info', [
            'text' => [
                'en' => 'en',
                'de' => 'de'
            ]
        ]);

        $this->assertEquals('<p>en</p>', $field->text());

        // text template
        $field = new Field('info', [
            'text' => '{{ page.title }}',
            'model' => new Page([
                'slug'    => 'test',
                'content' => [
                    'title' => 'Test'
                ]
            ])
        ]);

        $this->assertEquals('<p>Test</p>', $field->text());
    }
}
