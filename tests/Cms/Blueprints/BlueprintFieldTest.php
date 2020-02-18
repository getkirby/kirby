<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class BlueprintFieldTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'blueprints' => [
                'fields/test' => [
                    'name'  => 'test',
                    'label' => 'Test',
                    'type'  => 'text'
                ]
            ]
        ]);
    }

    public function testFieldPropsDefaults()
    {
        $props = Blueprint::fieldProps([
            'name' => 'test',
            'type' => 'text'
        ]);

        $this->assertEquals('test', $props['name']);
        $this->assertEquals('text', $props['type']);
        $this->assertEquals('Test', $props['label']);
        $this->assertEquals('1/1', $props['width']);
    }

    public function testFieldTypeFromName()
    {
        $props = Blueprint::fieldProps([
            'name' => 'text',
        ]);

        $this->assertEquals('text', $props['name']);
        $this->assertEquals('text', $props['type']);
        $this->assertEquals('Text', $props['label']);
    }

    public function testMissingFieldName()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The field name is missing');

        $props = Blueprint::fieldProps([]);
    }

    public function testInvalidFieldType()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid field type ("test")');

        $props = Blueprint::fieldProps([
            'name' => 'test',
            'type' => 'test'
        ]);
    }

    public function testFieldError()
    {
        $props = Blueprint::fieldError('test', 'something went wrong');
        $expected = [
            'label' => 'Error',
            'name'  => 'test',
            'text'  => 'something went wrong',
            'theme' => 'negative',
            'type'  => 'info'
        ];

        $this->assertEquals($expected, $props);
    }

    public function testExtendField()
    {
        $props = Blueprint::fieldProps([
            'name'    => 'test',
            'extends' => 'fields/test'
        ]);

        $expected = [
            'label' => 'Test',
            'name'  => 'test',
            'type'  => 'text',
            'width' => '1/1'
        ];

        $this->assertEquals($expected, $props);
    }

    public function testExtendFieldFromString()
    {
        $props = Blueprint::fieldProps('fields/test');

        $this->assertEquals('test', $props['name']);
        $this->assertEquals('Test', $props['label']);
        $this->assertEquals('text', $props['type']);
    }

    public function testExtendFieldWithNonAssociativeOptions()
    {
        $this->app = $this->app->clone([
            'blueprints' => [
                'fields/another-test' => [
                    'name'  => 'test',
                    'label' => 'Test',
                    'type'  => 'textarea',
                    'buttons' => [
                        'bold',
                        'italic'
                    ]
                ]
            ]
        ]);


        $props = Blueprint::fieldProps([
            'extends' => 'fields/another-test',
            'buttons' => [
                'li'
            ]
        ]);

        $expected = [
            'buttons' => [
                'li'
            ],
            'label' => 'Test',
            'name'  => 'test',
            'type'  => 'textarea',
            'width' => '1/1'
        ];

        $this->assertEquals($expected, $props);
    }

    public function testNestedFields()
    {
        $props = Blueprint::fieldProps([
            'name'   => 'test',
            'type'   => 'structure',
            'fields' => [
                'headline' => [
                    'type' => 'text'
                ]
            ]
        ]);

        $this->assertEquals('headline', $props['fields']['headline']['name']);
        $this->assertEquals('Headline', $props['fields']['headline']['label']);
        $this->assertEquals('text', $props['fields']['headline']['type']);
        $this->assertEquals('1/1', $props['fields']['headline']['width']);
    }

    public function testFieldGroup()
    {
        $props = Blueprint::fieldProps([
            'name'   => 'test',
            'type'   => 'group',
            'fields' => [
                'headline' => [
                    'type' => 'text'
                ]
            ]
        ]);

        $expected = [
            'fields' => [
                'headline' => [
                    'label' => 'Headline',
                    'name'  => 'headline',
                    'type'  => 'text',
                    'width' => '1/1'
                ]
            ],
            'name' => 'test',
            'type' => 'group'
        ];

        $this->assertEquals($expected, $props);
    }
}
