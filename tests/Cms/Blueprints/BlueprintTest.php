<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class BlueprintTest extends TestCase
{
    protected $app;
    protected $model;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $this->model = new Page(['slug' => 'a']);
    }

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
            'model'   => $this->model,
            'columns' => $columns
        ]);

        $expected = [
            'main' => [
                'name'    => 'main',
                'label'   => 'Main',
                'columns' => [
                    [
                        'width' => '1/3',
                        'sections' => [
                            'main-info-0' => [
                                'headline' => 'Column (1/3)',
                                'type'     => 'info',
                                'text'     => 'No sections yet',
                                'name'     => 'main-info-0'
                            ]
                        ]
                    ],
                    [
                        'width' => '2/3',
                        'sections' => [
                            'main-info-1' => [
                                'headline' => 'Column (2/3)',
                                'type'     => 'info',
                                'text'     => 'No sections yet',
                                'name'     => 'main-info-1'
                            ]
                        ]
                    ]
                ],
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
            'model'    => $this->model,
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
            'model'  => $this->model,
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
            'model' => $this->model
        ]);

        $this->assertEquals('Test', $blueprint->title());
    }

    public function testTitleTranslated()
    {
        $blueprint = new Blueprint([
            'title' => ['en' => 'Test'],
            'model' => $this->model
        ]);

        $this->assertEquals('Test', $blueprint->title());
    }

    public function testTitleFromName()
    {
        $blueprint = new Blueprint([
            'model' => $this->model
        ]);

        $this->assertEquals('Default', $blueprint->title());

        $blueprint = new Blueprint([
            'model' => $this->model,
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
            'model' => $this->model,
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
            'model' => $this->model,
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

    public function testInvalidSectionType()
    {
        $blueprint = new Blueprint([
            'model' => $this->model,
            'sections' => [
                'main' => [
                    'type' => [
                        'headline' => [
                            'label' => 'Headline',
                            'name'  => 'headline',
                            'type'  => 'text',
                            'width' => '1/1'
                        ]
                    ]
                ]
            ]
        ]);

        try {
            $sections = $blueprint->tab('main')['columns'][0]['sections'];
        } catch (\Exception $e) {
            $this->assertNull($e->getMessage(), 'Failed to get sections.');
        }

        $this->assertEquals(true, is_array($sections));
        $this->assertEquals(1, sizeof($sections));
        $this->assertEquals(true, array_key_exists('main', $sections));
        $this->assertEquals(true, array_key_exists('headline', $sections['main']));
        $this->assertEquals('Invalid section type for section "main"', $sections['main']['headline']);
    }

    public function testSectionTypeFromName()
    {
        // with options
        $blueprint = new Blueprint([
            'model' => $this->model,
            'sections' => [
                'info' => [
                ]
            ]
        ]);

        $this->assertEquals('info', $blueprint->sections()['info']->type());

        // by just passing true
        $blueprint = new Blueprint([
            'model' => $this->model,
            'sections' => [
                'info' => true
            ]
        ]);

        $this->assertEquals('info', $blueprint->sections()['info']->type());
    }
}
