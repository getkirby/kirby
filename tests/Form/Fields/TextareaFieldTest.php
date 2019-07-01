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
        $this->assertEquals(['accept' => '*'], $field->uploads());
        $this->assertTrue($field->save());
    }

    public function testButtonsDisabled()
    {
        $field = new Field('textarea', [
            'buttons' => false
        ]);

        $this->assertFalse($field->buttons());
    }

    public function testButtonsArray()
    {
        $field = new Field('textarea', [
            'buttons' => [
                'bold',
                'italic'
            ]
        ]);

        $this->assertEquals(['bold', 'italic'], $field->buttons());
    }

    public function testDefaultTrimmed()
    {
        $field = new Field('textarea', [
            'default' => 'test '
        ]);

        $this->assertEquals('test', $field->default());
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

    public function testFilesWithInvalidInput()
    {
        $field = new Field('textarea', [
            'files' => 1
        ]);

        $this->assertEquals([], $field->files());
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

    public function testMinLength()
    {
        $field = new Field('textarea', [
            'value' => 'test',
            'minlength' => 5
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('minlength', $field->errors());
    }

    public function testUploads()
    {
        $field = new Field('textarea', [
            'value' => 'test',
            'uploads' => [
                'template' => 'test'
            ]
        ]);

        $this->assertEquals(['template' => 'test', 'accept' => '*'], $field->uploads());
    }

    public function testUploadsDisabled()
    {
        $field = new Field('textarea', [
            'value' => 'test',
            'uploads' => false,
        ]);

        $this->assertFalse($field->uploads());
    }

    public function testUploadsTemplate()
    {
        $field = new Field('textarea', [
            'value' => 'test',
            'uploads' => 'test'
        ]);

        $this->assertEquals(['template' => 'test', 'accept' => '*'], $field->uploads());
    }

    public function testUploadsWithInvalidInput()
    {
        $field = new Field('textarea', [
            'value' => 'test',
            'uploads' => 1,
        ]);

        $this->assertEquals(['accept' => '*'], $field->uploads());
    }

    public function testValueTrimmed()
    {
        $field = new Field('textarea', [
            'value' => 'test '
        ]);

        $this->assertEquals('test', $field->value());
    }
}
