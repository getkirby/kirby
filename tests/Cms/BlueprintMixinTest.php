<?php

namespace Kirby\Cms;

class BlueprintMixinTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        Blueprint::$mixins = [];
    }

    public function testMixinFromFile()
    {
        $app = new App([
            'roots' => [
                'blueprints' => __DIR__ . '/fixtures/blueprints'
            ]
        ]);

        $mixin = Blueprint::mixin('fields/headline');

        $this->assertEquals('text', $mixin['type']);
        $this->assertEquals('Headline', $mixin['label']);
    }

    public function testPluginMixinFromArray()
    {
        $app = new App([
            'blueprints' => [
                'fields/headline' => [
                    'label' => 'Headline',
                    'type'  => 'text'
                ]
            ]
        ]);

        $mixin = Blueprint::mixin('fields/headline');

        $this->assertEquals('text', $mixin['type']);
        $this->assertEquals('Headline', $mixin['label']);
    }

    public function testPluginMixinFromFile()
    {
        $app = new App([
            'blueprints' => [
                'fields/headline' => __DIR__ . '/fixtures/blueprints/fields/headline.yml'
            ]
        ]);

        $mixin = Blueprint::mixin('fields/headline');

        $this->assertEquals('text', $mixin['type']);
        $this->assertEquals('Headline', $mixin['label']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The mixin "fields/headline" does not exist
     */
    public function testNonExistingMixin()
    {
        $app = new App;
        Blueprint::mixin('fields/headline');
    }

    public function testBlueprintMixin()
    {
        new App([
            'blueprints' => [
                'contact' => [
                    'name'   => 'contact',
                    'title'  => 'Contact',
                    'fields' => [
                        'address' => [
                            'label' => 'Address',
                            'type'  => 'text'
                        ]
                    ]
                ]
            ]
        ]);


        $blueprint = new Blueprint([
            'extends' => 'contact',
            'title'   => 'My Contact',
            'model'   => new Page(['slug' => 'test']),
            'fields'  => [
                'address' => [
                    'label' => 'My Address'
                ]
            ]
        ]);

        $this->assertEquals('My Contact', $blueprint->title());
        $this->assertEquals('My Address', $blueprint->fields()->address()->label());
    }

    public function testTabMixin()
    {
        new App([
            'blueprints' => [
                'tabs/seo' => [
                    'label'   => 'SEO',
                    'icon'    => 'search',
                    'columns' => [
                        ['1/1' => 'test']
                    ]
                ]
            ]
        ]);

        $blueprint = new Blueprint([
            'name'  => 'test',
            'title' => 'Test',
            'model' => new Page(['slug' => 'test']),
            'tabs'  => [
                'seo' => 'tabs/seo'
            ],
            'sections' => [
                'test' => [
                    'type' => 'pages'
                ]
            ]
        ]);

        $tabs = $blueprint->tabs()->toArray();

        $this->assertEquals('SEO', $tabs[0]['label']);
        $this->assertEquals('search', $tabs[0]['icon']);
    }

    public function testSectionMixin()
    {
        new App([
            'blueprints' => [
                'sections/gallery' => [
                    'headline' => 'Gallery',
                    'type'     => 'files'
                ]
            ]
        ]);

        $blueprint = new Blueprint([
            'name'  => 'test',
            'title' => 'Test',
            'model' => new Page(['slug' => 'test']),
            'sections' => [
                'gallery' => 'sections/gallery'
            ]
        ]);

        $sections = $blueprint->sections()->toArray();

        $this->assertEquals('Gallery', $sections[0]['headline']);
        $this->assertEquals('files', $sections[0]['type']);
    }

    public function testFieldMixin()
    {
        new App([
            'blueprints' => [
                'fields/headline' => [
                    'label' => 'Headline',
                    'type'  => 'text'
                ]
            ]
        ]);

        $blueprint = new Blueprint([
            'name'  => 'test',
            'title' => 'Test',
            'model' => new Page(['slug' => 'test']),
            'fields' => [
                'headline' => 'fields/headline'
            ]
        ]);

        $fields = $blueprint->fields()->toArray();

        $this->assertEquals('Headline', $fields['headline']['label']);
        $this->assertEquals('text', $fields['headline']['type']);
    }

}
