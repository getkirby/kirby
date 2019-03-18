<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;

class TextareaFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = new Field('textarea');

        $this->assertEquals('textarea', $field->type());
        $this->assertEquals('textarea', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertEquals(null, $field->icon());
        $this->assertEquals(null, $field->placeholder());
        $this->assertEquals(true, $field->counter());
        $this->assertEquals(null, $field->maxlength());
        $this->assertEquals(null, $field->minlength());
        $this->assertEquals(null, $field->size());
        $this->assertEquals([], $field->files());
        $this->assertEquals([], $field->uploads());
        $this->assertTrue($field->save());
    }

    public function testMinLength()
    {
        $field = new Field('textarea', [
            'value' => 'test',
            'minlength' => 5
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('minlength', $field->errors());
    }

    public function testMaxLength()
    {
        $field = new Field('textarea', [
            'value'     => 'test',
            'maxlength' => 3
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('maxlength', $field->errors());
    }

    public function testFiles()
    {
        $field = new Field('textarea', [
            'value' => 'test',
            'files' => [
                'query' => 'page.images'
            ]
        ]);

        $this->assertEquals(['query' => 'page.images'], $field->files());
    }

    public function testFilesQuery()
    {
        $field = new Field('textarea', [
            'value' => 'test',
            'files' => 'page.images'
        ]);

        $this->assertEquals(['query' => 'page.images'], $field->files());
    }

    public function testUploads()
    {
        $field = new Field('textarea', [
            'value' => 'test',
            'uploads' => [
                'template' => 'test'
            ]
        ]);

        $this->assertEquals(['template' => 'test'], $field->uploads());
    }

    public function testUploadsTemplate()
    {
        $field = new Field('textarea', [
            'value' => 'test',
            'uploads' => 'test'
        ]);

        $this->assertEquals(['template' => 'test'], $field->uploads());
    }
}
