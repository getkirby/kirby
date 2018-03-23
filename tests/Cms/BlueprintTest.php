<?php

namespace Kirby\Cms;

class BlueprintTest extends TestCase
{

    public function blueprint(array $props = []): Blueprint
    {
        return new Blueprint(array_merge($this->props(), $props));
    }

    public function props(): array
    {
        return [
            'name'     => 'test',
            'options'  => $this->options(),
            'sections' => $this->sections(),
            'tabs'     => $this->tabs(),
            'title'    => 'Test',
            'model'    => new Page(['slug' => 'test'])
        ];
    }

    public function tabs(): array
    {
        return [
            [
                'name'    => 'testTab',
                'label'   => 'Test Tab',
                'icon'    => 'settings',
                'columns' => $this->columns()
            ]
        ];
    }

    public function columns(): array
    {
        return [
            '1/2' => 'testSection'
        ];
    }

    public function sections(): array
    {
        return [
            [
                'name'   => 'testSection',
                'type'   => 'fields',
                'fields' => $this->fields()
            ]
        ];
    }

    public function options(): array
    {
        return [
            [
                'delete'   => true,
                'preview'  => true,
                'template' => true
            ]
        ];
    }

    public function fields(): array
    {
        return [
            [
                'name'  => 'testField',
                'label' => 'Test Field',
                'type'  => 'text'
            ]
        ];
    }

    public function testName()
    {
        $this->assertEquals('test', $this->blueprint()->name());
    }

    public function testOptions()
    {
        $this->assertEquals($this->options(), $this->blueprint()->options());
    }

    public function testSection()
    {
        $blueprint = $this->blueprint();
        $this->assertInstanceOf(BlueprintSection::class, $blueprint->section('testSection'));
        $this->assertEquals('testSection', $blueprint->section('testSection')->name());
    }

    public function testSections()
    {
        $blueprint = $this->blueprint();

        $this->assertInstanceOf(Collection::class, $blueprint->sections());
        $this->assertCount(1, $blueprint->sections());
        $this->assertInstanceOf(BlueprintSection::class, $blueprint->sections()->first());
        $this->assertEquals('testSection', $blueprint->sections()->first()->name());
    }

    public function testEmptyTabs()
    {
        $blueprint = $this->blueprint(['tabs' => []]);

        $this->assertInstanceOf(BlueprintTabs::class, $blueprint->tabs());
    }

    public function testTabs()
    {
        $blueprint = $this->blueprint();

        $this->assertInstanceOf(BlueprintTabs::class, $blueprint->tabs());
    }

    public function testTitle()
    {
        $this->assertEquals('Test', $this->blueprint()->title());
    }

    public function testIsDefault()
    {
        $blueprint = $this->blueprint([
            'name' => 'default'
        ]);

        $this->assertTrue($blueprint->isDefault());
    }

    public function testIsNotDefault()
    {
        $this->assertFalse($this->blueprint()->isDefault());
    }

    public function testLoad()
    {
        new App([
            'roots' => [
                'blueprints' => __DIR__ . '/fixtures/blueprints'
            ]
        ]);

        $blueprint = Blueprint::factory('test', null, new Page(['slug' => 'test']));

        $this->assertCount(3, $blueprint->sections());
        $this->assertEquals('fields', $blueprint->sections()->first()->name());
        $this->assertEquals('gallery', $blueprint->sections()->last()->name());
        $this->assertCount(2, $blueprint->fields());
        $this->assertEquals('title', $blueprint->fields()->first()->name());
        $this->assertEquals('text', $blueprint->fields()->last()->name());
    }

    public function testLoadFields()
    {
        new App([
            'roots' => [
                'blueprints' => __DIR__ . '/fixtures/blueprints'
            ]
        ]);

        $blueprint = Blueprint::factory('fields', null, new Page(['slug' => 'test']));
        $this->assertEquals('fields', $blueprint->sections()->first()->name());
    }

}
