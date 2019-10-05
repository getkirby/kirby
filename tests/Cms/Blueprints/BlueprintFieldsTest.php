<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class BlueprintFieldsTest extends TestCase
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
                    'label' => 'Test',
                    'type'  => 'text'
                ]
            ]
        ]);
    }

    public function testEmptyFields()
    {
        $fields = Blueprint::fieldsProps(false);
        $this->assertEquals([], $fields);
    }

    public function testNameOnlyField()
    {
        $fields = Blueprint::fieldsProps([
            'text' => true
        ]);

        $expected = [
            'text' => [
                'label' => 'Text',
                'name'  => 'text',
                'type'  => 'text',
                'width' => '1/1'
            ]
        ];

        $this->assertEquals($expected, $fields);
    }

    public function testFieldFromString()
    {
        $fields = Blueprint::fieldsProps([
            'hello' => 'fields/test'
        ]);

        $expected = [
            'hello' => [
                'label' => 'Test',
                'name'  => 'hello',
                'type'  => 'text',
                'width' => '1/1'
            ]
        ];

        $this->assertEquals($expected, $fields);
    }

    public function testFieldGroup()
    {
        $fields = Blueprint::fieldsProps([
            'header' => [
                'type'   => 'group',
                'fields' => [
                    'headline' => [
                        'type' => 'text'
                    ],
                    'intro' => [
                        'type' => 'textarea'
                    ]
                ]
            ],
            'text' => [
                'type' => 'textarea'
            ]
        ]);

        $expected = [
            'headline' => [
                'label' => 'Headline',
                'name'  => 'headline',
                'type'  => 'text',
                'width' => '1/1'
            ],
            'intro' => [
                'label' => 'Intro',
                'name'  => 'intro',
                'type'  => 'textarea',
                'width' => '1/1'
            ],
            'text' => [
                'label' => 'Text',
                'name'  => 'text',
                'type'  => 'textarea',
                'width' => '1/1'
            ]
        ];

        $this->assertEquals($expected, $fields);
    }

    public function testMultipleFieldGroups()
    {
        $fields = Blueprint::fieldsProps([
            'header' => [
                'type'   => 'group',
                'fields' => [
                    'headline' => [
                        'type' => 'text'
                    ],
                    'intro' => [
                        'type' => 'textarea'
                    ]
                ]
            ],
            'body' => [
                'type'   => 'group',
                'fields' => [
                    'tags' => [
                        'type' => 'tags'
                    ],
                    'text' => [
                        'type' => 'textarea'
                    ]
                ]
            ]
        ]);

        $expected = [
            'headline' => [
                'label' => 'Headline',
                'name'  => 'headline',
                'type'  => 'text',
                'width' => '1/1'
            ],
            'intro' => [
                'label' => 'Intro',
                'name'  => 'intro',
                'type'  => 'textarea',
                'width' => '1/1'
            ],
            'tags' => [
                'label' => 'Tags',
                'name'  => 'tags',
                'type'  => 'tags',
                'width' => '1/1'
            ],
            'text' => [
                'label' => 'Text',
                'name'  => 'text',
                'type'  => 'textarea',
                'width' => '1/1'
            ]
        ];

        $this->assertEquals($expected, $fields);
    }

    public function testFieldError()
    {
        $props = Blueprint::fieldsProps([
            'test' => [
                'type' => 'invalid'
            ]
        ]);

        $expected = [
            'test' => [
                'label' => 'Error',
                'name'  => 'test',
                'text'  => 'Invalid field type ("invalid")',
                'theme' => 'negative',
                'type'  => 'info'
            ]
        ];

        $this->assertEquals($expected, $props);
    }
}
