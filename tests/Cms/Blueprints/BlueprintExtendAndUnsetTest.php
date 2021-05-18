<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class BlueprintExtendAndUnsetTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'blueprints' => [
                'pages/base' => [
                    'title'  => 'base',
                    'model'  => 'page',
                    'tabs'   => [
                        'content' => [
                            'sections' => [
                                'pages' => [
                                    'type' => 'pages'
                                ],
                                'files' => [
                                    'type' => 'files'
                                ]
                            ]
                        ],
                        'additional' => [
                            'columns' => [
                                'left' => [
                                    'width'  => '1/2',
                                    'fields' => [
                                        'headline' => [
                                            'label' => 'Headline',
                                            'type' => 'text'
                                        ],
                                    ]
                                ],
                                'right' => [
                                    'width'  => '1/2',
                                    'fields' => [
                                        'text' => [
                                            'label' => 'Text',
                                            'type' => 'text'
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'seo' => [
                            'fields' => [
                                'seoTitle' => [
                                    'label' => 'SEO Title',
                                    'type' => 'text'
                                ],
                                'seoDescription' => [
                                    'label' => 'SEO Description',
                                    'type' => 'text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function testExtendAndUnsetTab()
    {
        $blueprint = new Blueprint([
            'title'  => 'extended',
            'model'  => new Page(['slug' => 'test']),
            'extends' => 'pages/base',
            'tabs'  => [
                'seo'  => false
            ]
        ]);

        $this->assertSame('extended', $blueprint->title());
        $this->assertCount(2, $blueprint->tabs());
        $this->assertIsArray($blueprint->tab('content'));
        $this->assertIsNotArray($blueprint->tab('seo'));
    }

    public function testExtendAndUnsetSection()
    {
        $blueprint = new Blueprint([
            'title'  => 'extended',
            'model'  => new Page(['slug' => 'test']),
            'extends' => 'pages/base',
            'tabs'  => [
                'content'  => [
                    'sections' => [
                        'files' => false
                    ]
                ]
            ]
        ]);

        try {
            $sections = $blueprint->tab('content')['columns'][0]['sections'];
        } catch (\Exception $e) {
            $this->assertNull($e->getMessage(), 'Failed to getg sections.');
        }

        $this->assertSame('extended', $blueprint->title());
        $this->assertIsArray($sections);
        $this->assertCount(1, $sections);
        $this->assertArrayHasKey('pages', $sections);
        $this->assertArrayNotHasKey('files', $sections);
    }

    public function testExtendAndUnsetFields()
    {
        $blueprint = new Blueprint([
            'title'  => 'extended',
            'model'  => new Page(['slug' => 'test']),
            'extends' => 'pages/base',
            'tabs'  => [
                'seo' => [
                    'fields' => [
                        'seoDescription' => false
                    ]
                ]
            ]
        ]);

        try {
            $fields = $blueprint->tab('seo')['columns'][0]['sections']['seo-fields']['fields'];
        } catch (\Exception $e) {
            $this->assertNull($e->getMessage(), 'Failed to get fields.');
        }

        $this->assertSame('extended', $blueprint->title());
        $this->assertIsArray($fields);
        $this->assertCount(1, $fields);
        $this->assertArrayHasKey('seoTitle', $fields);
        $this->assertArrayNotHasKey('seoDescription', $fields);
    }

    public function testExtendAndUnsetColumns()
    {
        $blueprint = new Blueprint([
            'title'   => 'extended',
            'model'   => new Page(['slug' => 'test']),
            'extends' => 'pages/base',
            'tabs'    => [
                'additional' => [
                    'columns' => [
                        'left' => [
                            'width' => '1/1'
                        ],
                        'right' => false
                    ]
                ]
            ]
        ]);

        $tab = $blueprint->tab('additional');

        $this->assertIsArray($tab);
        $this->assertCount(1, $tab['columns']);
        $this->assertArrayHasKey('left', $tab['columns']);
        $this->assertArrayNotHasKey('right', $tab['columns']);
        $this->assertSame('1/1', $tab['columns']['left']['width']);
    }
}
