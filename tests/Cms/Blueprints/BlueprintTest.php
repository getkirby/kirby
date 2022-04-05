<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\Blueprint
 */
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

    /**
     * @covers ::__construct
     */
    public function testConstructWithoutModel()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('A blueprint model is required');

        new Blueprint([]);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructInvalidModel()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid blueprint model');

        new Blueprint(['model' => new \stdClass()]);
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
                'icon'    => null,
                'label'   => 'Main',
                'link'    => '/pages/a/?tab=main',
                'name'    => 'main'
            ]
        ];

        $this->assertSame($expected, $blueprint->toArray()['tabs']);
        $this->assertSame($expected['main'], $blueprint->tab());
    }

    /**
     * @covers ::__debugInfo
     */
    public function testDebugInfo()
    {
        $blueprint = new Blueprint([
            'model' => $this->model,
            'name'  => 'default'
        ]);

        $expected = [
            'name'  => 'default',
            'title' => 'Default',
            'tabs'  => []
        ];

        $this->assertSame($expected, $blueprint->__debugInfo());
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
                'icon'    => null,
                'link'    => '/pages/a/?tab=main'
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
                'icon'    => null,
                'link'    => '/pages/a/?tab=main'
            ]
        ];

        $this->assertEquals($expected, $blueprint->toArray()['tabs']);
    }

    /**
     * @covers ::title
     */
    public function testTitle()
    {
        $blueprint = new Blueprint([
            'title' => 'Test',
            'model' => $this->model
        ]);

        $this->assertEquals('Test', $blueprint->title());
    }

    /**
     * @covers ::title
     */
    public function testTitleTranslated()
    {
        $blueprint = new Blueprint([
            'title' => ['en' => 'Test'],
            'model' => $this->model
        ]);

        $this->assertEquals('Test', $blueprint->title());
    }

    /**
     * @covers ::title
     */
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

    /**
     * @covers ::extend
     */
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
            'model'   => new Page(['slug' => 'test'])
        ]);

        $this->assertSame('Extension Test', $blueprint->title());
    }

    /**
     * @covers ::extend
     */
    public function testExtendWithInvalidSnippet()
    {
        $blueprint = new Blueprint([
            'extends' => 'notFound',
            'model'   => new Page(['slug' => 'test'])
        ]);

        $this->assertSame('Default', $blueprint->title());
    }

    /**
     * @covers ::extend
     */
    public function testExtendMultiple()
    {
        new App([
            'blueprints' => [
                'props/after' => ['after' => 'foo'],
                'props/before' => ['before' => 'bar'],
                'props/required' => ['required' => true],
                'props/text' => ['type' => 'text'],
                'props/translatable' => ['translatable' => false],
                'props/width' => ['width' => '1/3']
            ]
        ]);

        $blueprint = new Blueprint([
            'model' => new Page(['slug' => 'test']),
            'fields' => [
                'test' => [
                    'label' => 'Test',
                    'extends'  => [
                        'props/after',
                        'props/before',
                        'props/required',
                        'props/text',
                        'props/translatable',
                        'props/width',
                    ]
                ]
            ]
        ]);

        $field = $blueprint->field('test');

        $this->assertSame('foo', $field['after']);
        $this->assertSame('bar', $field['before']);
        $this->assertSame(true, $field['required']);
        $this->assertSame('text', $field['type']);
        $this->assertSame(false, $field['translatable']);
        $this->assertSame('1/3', $field['width']);
    }

    /**
     * @covers ::factory
     * @covers ::find
     */
    public function testFactory()
    {
        Blueprint::$loaded = [];

        $this->app = $this->app->clone([
            'blueprints' => [
                'pages/test' => ['title' => 'Test']
            ]
        ]);

        $blueprint = Blueprint::factory('pages/test', null, new Page(['slug' => 'test']));

        $this->assertSame('Test', $blueprint->title());
        $this->assertSame('pages/test', $blueprint->name());
    }

    /**
     * @covers ::factory
     * @covers ::find
     */
    public function testFactoryWithCallback()
    {
        Blueprint::$loaded = [];

        $this->app = $this->app->clone([
            'blueprints' => [
                'pages/test' => function () {
                    return ['title' => 'Test'];
                }
            ]
        ]);

        $blueprint = Blueprint::factory('pages/test', null, new Page(['slug' => 'test']));

        $this->assertSame('Test', $blueprint->title());
        $this->assertSame('pages/test', $blueprint->name());
    }

    /**
     * @covers ::factory
     */
    public function testFactoryForMissingBlueprint()
    {
        $blueprint = Blueprint::factory('notFound', null, new Page(['slug' => 'test']));
        $this->assertNull($blueprint);
    }

    /**
     * @covers ::fields
     * @covers ::field
     */
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

    /**
     * @covers ::fields
     */
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

    /**
     * @covers ::isDefault
     */
    public function testIsDefault()
    {
        $blueprint = new Blueprint([
            'model' => $this->model,
            'name'  => 'default'
        ]);

        $this->assertTrue($blueprint->isDefault());
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

    /**
     * @covers ::preset
     */
    public function testPreset()
    {
        $blueprint = new Blueprint([
            'model'  => $this->model,
            'preset' => 'page'
        ]);

        $preset = $blueprint->toArray();

        $this->assertSame('page', $preset['preset']);
        $this->assertSame('default', $preset['name']);
        $this->assertSame('Default', $preset['title']);
        $this->assertArrayHasKey('tabs', $preset);
        $this->assertArrayHasKey('main', $preset['tabs']);
        $this->assertNull($preset['tabs']['main']['icon']);
        $this->assertArrayHasKey('columns', $preset['tabs']['main']);
        $this->assertSame('Main', $preset['tabs']['main']['label']);
        $this->assertSame('/pages/a/?tab=main', $preset['tabs']['main']['link']);
        $this->assertSame('main', $preset['tabs']['main']['name']);
    }
}
