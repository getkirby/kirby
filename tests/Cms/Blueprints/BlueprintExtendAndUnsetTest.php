<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class BlueprintExtendAndUnset extends TestCase
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
            'model'  => 'page',
            'extends' => 'pages/base',
            'tabs'  => [
                'seo'  => false
            ]
        ]);

        $this->assertEquals('extended', $blueprint->title());
        $this->assertEquals(1, sizeof($blueprint->tabs()));
        $this->assertEquals(false, is_array($blueprint->tab('seo')));
        $this->assertEquals(true, is_array($blueprint->tab('content')));
    }

    public function testExtendAndUnsetSection()
    {
        $blueprint = new Blueprint([
            'title'  => 'extended',
            'model'  => 'page',
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

        $this->assertEquals('extended', $blueprint->title());
        $this->assertEquals(true, is_array($sections));
        $this->assertEquals(1, sizeof($sections));
        $this->assertEquals(true, array_key_exists('pages', $sections));
        $this->assertEquals(false, array_key_exists('files', $sections));
    }

    public function testExtendAndUnsetFields()
    {
        $blueprint = new Blueprint([
            'title'  => 'extended',
            'model'  => 'page',
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

        $this->assertEquals('extended', $blueprint->title());
        $this->assertEquals(true, is_array($fields));
        $this->assertEquals(1, sizeof($fields));
        $this->assertEquals(true, array_key_exists('seoTitle', $fields));
        $this->assertEquals(false, array_key_exists('seoDescription', $fields));
    }
}
