<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\Fieldset
 */
class FieldsetTest extends TestCase
{
    public function testConstruct()
    {
        $fieldset = new Fieldset([
            'type' => 'test'
        ]);

        $this->assertSame('test', $fieldset->type());
        $this->assertSame('Test', $fieldset->name());
        $this->assertFalse($fieldset->disabled());
        $this->assertFalse($fieldset->editable());
        $this->assertNull($fieldset->icon());
        $this->assertTrue($fieldset->translate());
    }

    public function testConstructWithMissingType()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The fieldset type is missing');
        $fieldset = new Fieldset();
    }

    /**
     * @covers ::disabled
     */
    public function testDisabled()
    {
        $fieldset = new Fieldset([
            'type'     => 'test',
            'disabled' => true
        ]);

        $this->assertTrue($fieldset->disabled());
    }

    /**
     * @covers ::editable
     */
    public function testEditable()
    {
        $fieldset = new Fieldset([
            'type'   => 'test',
            'fields' => [
                'text' => [
                    'type' => 'text'
                ]
            ]
        ]);

        $this->assertTrue($fieldset->editable());
    }

    /**
     * @covers ::editable
     */
    public function testEditableWhenDisabled()
    {
        $fieldset = new Fieldset([
            'type'     => 'test',
            'editable' => false,
            'fields'   => [
                'text' => [
                    'type' => 'text'
                ]
            ]
        ]);

        $this->assertFalse($fieldset->editable());
    }

    /**
     * @covers ::fields
     */
    public function testFields()
    {
        $fieldset = new Fieldset([
            'type'   => 'test',
            'fields' => [
                'text' => [
                    'type' => 'text'
                ]
            ]
        ]);

        $this->assertSame('text', $fieldset->fields()['text']['type']);
    }

    /**
     * @covers ::fields
     */
    public function testFieldsInTabs()
    {
        $fieldset = new Fieldset([
            'type'   => 'test',
            'tabs' => [
                'content' => [
                    'fields' => [
                        'text' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertSame('text', $fieldset->fields()['text']['type']);
    }

    /**
     * @covers ::form
     */
    public function testForm()
    {
        $fieldset = new Fieldset([
            'type' => 'test',
        ]);

        $form = $fieldset->form([
            'text' => [
                'type' => 'text'
            ]
        ]);

        $this->assertInstanceOf('\Kirby\Cms\Form', $form);
    }

    /**
     * @covers ::icon
     */
    public function testIcon()
    {
        $fieldset = new Fieldset([
            'type' => 'test',
            'icon' => 'test'
        ]);

        $this->assertSame('test', $fieldset->icon());
    }

    /**
     * @covers ::label
     */
    public function testLabel()
    {
        $fieldset = new Fieldset([
            'type'  => 'test',
            'label' => 'Test'
        ]);

        $this->assertSame('Test', $fieldset->label());
    }

    /**
     * @covers ::label
     */
    public function testLabelWithTranslation()
    {
        $fieldset = new Fieldset([
            'type'  => 'test',
            'label' => [
                'en' => 'English',
                'de' => 'Deutsch'
            ]
        ]);

        $this->assertSame('English', $fieldset->label());
    }

    /**
     * @covers ::model
     */
    public function testModel()
    {
        $fieldset = new Fieldset([
            'type'   => 'test',
            'parent' => $model = new Page(['slug' => 'test'])
        ]);

        $this->assertSame($model, $fieldset->model());
    }

    /**
     * @covers ::name
     */
    public function testName()
    {
        $fieldset = new Fieldset([
            'type'  => 'test',
            'name'  => 'test'
        ]);

        $this->assertSame('test', $fieldset->name());
    }

    /**
     * @covers ::name
     */
    public function testNameTranslated()
    {
        $fieldset = new Fieldset([
            'type'  => 'test',
            'name'  => [
                'en' => 'English name',
                'de' => 'Deutscher Name',
            ]
        ]);

        $this->assertSame('English name', $fieldset->name());
    }

    /**
     * @covers ::name
     */
    public function testNameFromTitle()
    {
        $fieldset = new Fieldset([
            'type'  => 'test',
            'title' => 'Test Title'
        ]);

        $this->assertSame('Test Title', $fieldset->name());
    }

    /**
     * @covers ::name
     */
    public function testNameFromTitleTranslated()
    {
        $fieldset = new Fieldset([
            'type'  => 'test',
            'title' => [
                'en' => 'English name',
                'de' => 'Deutscher Name',
            ]
        ]);

        $this->assertSame('English name', $fieldset->name());
    }

    /**
     * @covers ::preview
     */
    public function testPreview()
    {
        $fieldset = new Fieldset([
            'type'    => 'test',
            'preview' => 'test'
        ]);

        $this->assertSame('test', $fieldset->preview());
    }

    /**
     * @covers ::tabs
     */
    public function testTabs()
    {
        $fieldset = new Fieldset([
            'type' => 'test',
            'fields' => [
                'foo' => ['type' => 'text'],
                'bar' => ['type' => 'text']
            ]
        ]);

        $this->assertIsArray($fieldset->tabs());
        $this->assertArrayHasKey('content', $fieldset->tabs());
        $this->assertArrayHasKey('fields', $fieldset->tabs()['content']);
        $this->assertIsArray($fieldset->tabs()['content']['fields']);
        $this->assertCount(2, $fieldset->tabs()['content']['fields']);
    }

    /**
     * @covers ::translate
     */
    public function testTranslate()
    {
        $fieldset = new Fieldset([
            'type'      => 'test',
            'translate' => false
        ]);

        $this->assertFalse($fieldset->translate());
    }

    /**
     * @covers ::type
     */
    public function testType()
    {
        $fieldset = new Fieldset([
            'type' => 'test',
        ]);

        $this->assertSame('test', $fieldset->type());
    }

    /**
     * @covers ::toArray
     */
    public function testToArray()
    {
        $fieldset = new Fieldset([
            'type' => 'test',
        ]);

        $expected = [
            'disabled'  => false,
            'editable'  => false,
            'icon'      => null,
            'label'     => null,
            'name'      => 'Test',
            'preview'   => null,
            'tabs'      => [
                'content' => [
                    'fields' => []
                ]
            ],
            'translate' => true,
            'type'      => 'test',
            'unset'     => false,
            'wysiwyg'   => false,
        ];

        $this->assertSame($expected, $fieldset->toArray());
    }

    /**
     * @covers ::unset
     */
    public function testUnset()
    {
        $fieldset = new Fieldset([
            'type'  => 'test',
            'unset' => true
        ]);

        $this->assertTrue($fieldset->unset());
    }

    /**
     * @covers ::wysiwyg
     */
    public function testWysiwyg()
    {
        $fieldset = new Fieldset([
            'type'    => 'test',
            'wysiwyg' => true
        ]);

        $this->assertTrue($fieldset->wysiwyg());
    }
}
