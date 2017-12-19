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
            'name'    => 'test',
            'options' => $this->options(),
            'tabs'    => $this->tabs(),
            'title'   => 'Test',
        ];
    }

    public function tabs(): array
    {
        return [
            [
                'id'      => $id = 'testTab',
                'name'    => $id,
                'label'   => 'Test Tab',
                'icon'    => 'settings',
                'columns' => $this->columns()
            ]
        ];
    }

    public function columns(): array
    {
        return [
            [
                'name'     => 'test',
                'width'    => '1/2',
                'sections' => $this->sections(),
                'id'       => 'test'
            ]
        ];
    }

    public function sections(): array
    {
        return [
            [
                'id'     => $id = 'testSection',
                'name'   => $id,
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
                'id'    => $id = 'testField',
                'name'  => $id,
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
        $this->assertInstanceOf(BlueprintObject::class, $this->blueprint()->options());
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

        $this->assertInstanceOf(Collection::class, $blueprint->tabs());
        $this->assertCount(0, $blueprint->tabs());
    }

    public function testTab()
    {
        $blueprint = $this->blueprint();
        $this->assertInstanceOf(BlueprintTab::class, $blueprint->tab('testTab'));
        $this->assertEquals('testTab', $blueprint->tab('testTab')->name());
    }

    public function testTabs()
    {
        $blueprint = $this->blueprint();

        $this->assertInstanceOf(Collection::class, $blueprint->tabs());
        $this->assertCount(1, $blueprint->tabs());
        $this->assertInstanceOf(BlueprintTab::class, $blueprint->tabs()->first());
        $this->assertEquals('testTab', $blueprint->tabs()->first()->name());
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

    public function testToArray()
    {
        $this->assertEquals($this->props(), $this->blueprint()->toArray());
    }

    public function testToLayout()
    {
        $tabs = $this->blueprint()->toLayout();

        $this->assertEquals('testTab', $tabs[0]['name']);
        $this->assertEquals('settings', $tabs[0]['icon']);
        $this->assertEquals('1/2', $tabs[0]['columns'][0]['width']);
        $this->assertEquals(['testSection'], $tabs[0]['columns'][0]['sections']);
    }

    public function testLoad()
    {
        $blueprint = Blueprint::load(__DIR__ . '/fixtures/blueprints/test.yml');

        $this->assertCount(1, $blueprint->tabs());
        $this->assertEquals('test', $blueprint->tabs()->first()->name());
        $this->assertCount(3, $blueprint->sections());
        $this->assertEquals('fields', $blueprint->sections()->first()->name());
        $this->assertEquals('gallery', $blueprint->sections()->last()->name());
        $this->assertCount(2, $blueprint->fields());
        $this->assertEquals('title', $blueprint->fields()->first()->name());
        $this->assertEquals('text', $blueprint->fields()->last()->name());
    }

    public function testLoadFields()
    {
        $blueprint = Blueprint::load(__DIR__ . '/fixtures/blueprints/fields.yml');
        $this->assertCount(1, $blueprint->tabs());
        $this->assertEquals('main', $blueprint->tabs()->first()->name());
        $this->assertCount(1, $blueprint->sections());
        $this->assertEquals('fields', $blueprint->sections()->first()->name());
    }

}
