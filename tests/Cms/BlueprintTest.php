<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class BlueprintTest extends TestCase
{

    public function testConvertColumnsToTabs()
    {
        $columns = [
            [
                'width'    => '1/3',
                'sections' => []
            ],
            [
                'width'    => '2/3',
                'sections' => []
            ]
        ];

        $blueprint = new Blueprint([
            'model'   => 'test',
            'columns' => $columns
        ]);

        $expected = [
            'main' => [
                'name'    => 'main',
                'label'   => 'Main',
                'columns' => $columns,
                'icon'    => null
            ]
        ];

        $this->assertEquals($expected, $blueprint->toArray()['tabs']);
    }

    public function testSectionsToColumns()
    {
        $sections = [
            'pages' => [
                'name' => 'pages',
                'type' => 'pages'
            ],
            'files' => [
                'name' => 'files',
                'type' => 'files'
            ]
        ];

        $blueprint = new Blueprint([
            'model'    => 'test',
            'sections' => $sections
        ]);

        $expected = [
            'main' => [
                'name'    => 'main',
                'label'   => 'Main',
                'columns' => [
                    [
                        'width'    => '1/1',
                        'sections' => $sections
                    ]
                ],
                'icon'    => null
            ]
        ];

        $this->assertEquals($expected, $blueprint->toArray()['tabs']);
    }

    public function testFieldsToSections()
    {
        $fields = [
            'headline' => [
                'label' => 'Headline',
                'name'  => 'headline',
                'type'  => 'text',
                'width' => '1/1'
            ]
        ];

        $blueprint = new Blueprint([
            'model'  => 'test',
            'fields' => $fields
        ]);

        $expected = [
            'main' => [
                'name'    => 'main',
                'label'   => 'Main',
                'columns' => [
                    [
                        'width'    => '1/1',
                        'sections' => [
                            'main-fields' => [
                                'name'   => 'main-fields',
                                'type'   => 'fields',
                                'fields' => $fields
                            ]
                        ]
                    ]
                ],
                'icon'    => null
            ]
        ];

        $this->assertEquals($expected, $blueprint->toArray()['tabs']);
    }

    public function testTitle()
    {
        $blueprint = new Blueprint([
            'title' => 'Test',
            'model' => 'test'
        ]);

        $this->assertEquals('Test', $blueprint->title());
    }

    public function testTitleTranslated()
    {
        $blueprint = new Blueprint([
            'title' => ['en' => 'Test'],
            'model' => 'test'
        ]);

        $this->assertEquals('Test', $blueprint->title());
    }

    public function testTitleFromName()
    {
        $blueprint = new Blueprint([
            'model' => 'test'
        ]);

        $this->assertEquals('Default', $blueprint->title());

        $blueprint = new Blueprint([
            'model' => 'test',
            'name'  => 'test'
        ]);

        $this->assertEquals('Test', $blueprint->title());
    }

    public function testExtend()
    {
        new App([
            'blueprints' => [
                'test' => [
                    'title' => 'Extension Test'
                ]
            ]
        ]);

        $blueprint = new Blueprint([
            'extends' => 'test',
            'model'   => 'test'
        ]);

        $this->assertEquals('Extension Test', $blueprint->title());
    }

    public function testFields()
    {
        $blueprint = new Blueprint([
            'model' => 'test',
            'fields' => $fields = [
                'test' => [
                    'type'  => 'text',
                    'name'  => 'test',
                    'label' => 'Test',
                    'width' => '1/1'
                ]
            ]
        ]);

        $this->assertEquals($fields, $blueprint->fields());
        $this->assertEquals($fields['test'], $blueprint->field('test'));
    }

    public function testNestedFields()
    {
        $blueprint = new Blueprint([
            'model' => 'test',
            'fields' => $fields = [
                'test' => [
                    'type'   => 'structure',
                    'fields' => [
                        'child-field' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertCount(1, $blueprint->fields());
        $this->assertArrayHasKey('test', $blueprint->fields());
        $this->assertArrayNotHasKey('child-field', $blueprint->fields());

    }

}
