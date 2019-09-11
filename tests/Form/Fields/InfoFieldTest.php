<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\Page;

class InfoFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('info');

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
        $field = $this->field('info', [
            'text' => 'test'
        ]);

        $this->assertEquals('<p>test</p>', $field->text());

        // translated text
        $field = $this->field('info', [
            'text' => [
                'en' => 'en',
                'de' => 'de'
            ]
        ]);

        $this->assertEquals('<p>en</p>', $field->text());

        // text template
        $field = $this->field('info', [
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
