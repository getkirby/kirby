<?php

namespace Kirby\Form\Fields;

class TextareaFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('textarea');

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
        $field = $this->field('textarea', [
            'buttons' => false
        ]);

        $this->assertFalse($field->buttons());
    }

    public function testButtonsArray()
    {
        $field = $this->field('textarea', [
            'buttons' => [
                'bold',
                'italic'
            ]
        ]);

        $this->assertEquals(['bold', 'italic'], $field->buttons());
    }

    public function testDefaultTrimmed()
    {
        $field = $this->field('textarea', [
            'default' => 'test '
        ]);

        $this->assertEquals('test', $field->default());
    }

    public function testFiles()
    {
        $field = $this->field('textarea', [
            'value' => 'test',
            'files' => [
                'query' => 'page.images'
            ]
        ]);

        $this->assertEquals(['query' => 'page.images'], $field->files());
    }

    public function testFilesQuery()
    {
        $field = $this->field('textarea', [
            'value' => 'test',
            'files' => 'page.images'
        ]);

        $this->assertEquals(['query' => 'page.images'], $field->files());
    }

    public function testFilesWithInvalidInput()
    {
        $field = $this->field('textarea', [
            'files' => 1
        ]);

        $this->assertEquals([], $field->files());
    }

    public function testMaxLength()
    {
        $field = $this->field('textarea', [
            'value'     => 'test',
            'maxlength' => 3
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('maxlength', $field->errors());
    }

    public function testMinLength()
    {
        $field = $this->field('textarea', [
            'value' => 'test',
            'minlength' => 5
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('minlength', $field->errors());
    }

    public function testUploads()
    {
        $field = $this->field('textarea', [
            'value' => 'test',
            'uploads' => [
                'template' => 'test'
            ]
        ]);

        $this->assertEquals(['template' => 'test', 'accept' => '*'], $field->uploads());
    }

    public function testUploadsDisabled()
    {
        $field = $this->field('textarea', [
            'value' => 'test',
            'uploads' => false,
        ]);

        $this->assertFalse($field->uploads());
    }

    public function testUploadsParent()
    {
        $field = $this->field('textarea', [
            'value' => 'test',
            'uploads' => [
                'parent' => 'page.parent'
            ]
        ]);

        $this->assertEquals(['parent' => 'page.parent', 'accept' => '*'], $field->uploads());
    }

    public function testUploadsTemplate()
    {
        $field = $this->field('textarea', [
            'value' => 'test',
            'uploads' => 'test'
        ]);

        $this->assertEquals(['template' => 'test', 'accept' => '*'], $field->uploads());
    }

    public function testUploadsWithInvalidInput()
    {
        $field = $this->field('textarea', [
            'value' => 'test',
            'uploads' => 1,
        ]);

        $this->assertEquals(['accept' => '*'], $field->uploads());
    }

    public function testValueTrimmed()
    {
        $field = $this->field('textarea', [
            'value' => 'test '
        ]);

        $this->assertEquals('test', $field->value());
    }
}
