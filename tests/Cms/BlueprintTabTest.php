<?php

namespace Kirby\Cms;

use Kirby\Form\Field;
use Kirby\Form\Fields;

class BlueprintTabTest extends TestCase
{

    public function tab(array $props = [])
    {
        return new BlueprintTab(array_merge([
            'name'    => 'test',
            'columns' => $this->columns(),
            'model'   => new Page(['slug' => 'test'])
        ], $props));
    }

    public function columns()
    {
        return [
            'left' => [
                'width'    => '1/3',
                'sections' => [
                    [
                        'name'   => 'fields',
                        'type'   => 'fields',
                        'fields' => [
                            [
                                'name' => 'title',
                                'type' => 'text'
                            ],
                            [
                                'name' => 'text',
                                'type' => 'textarea'
                            ]
                        ]
                    ]
                ]
            ],
            'right' => [
                'width'    => '2/3',
                'sections' => [
                    [
                        'name'     => 'cover',
                        'type'     => 'files',
                        'headline' => 'Cover'
                    ],
                    [
                        'name'     => 'gallery',
                        'type'     => 'files',
                        'headline' => 'Gallery'
                    ]
                ]
            ]
        ];
    }

    public function testColumns()
    {
        $tab = $this->tab([
            'columns' => $this->columns()
        ]);

        $this->assertInstanceOf(Collection::class, $tab->columns());
        $this->assertCount(2, $tab->columns());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Please define at least one column for the tab
     */
    public function testEmptyColumns()
    {
        $tab = $this->tab([
            'columns' => []
        ]);
    }

    public function testFields()
    {
        $tab = $this->tab([
            'columns' => $this->columns(),
        ]);

        $this->assertInstanceOf(Fields::class, $tab->fields());
        $this->assertCount(2, $tab->fields());
    }

    public function testField()
    {
        $tab = $this->tab([
            'columns' => $this->columns(),
        ]);

        $this->assertInstanceOf(Field::class, $tab->field('title'));
        $this->assertEquals('title', $tab->field('title')->name());
    }

    public function testId()
    {
        $this->assertEquals('my-id', $this->tab(['id' => 'my-id'])->id());
    }

    public function testDefaultId()
    {
        $this->assertEquals('test', $this->tab()->id());
    }

    public function testLabel()
    {
        $this->assertEquals('Test', $this->tab(['label' => 'Test'])->label());
    }

    public function testDefaultLabel()
    {
        $this->assertNull($this->tab()->label());
    }

    public function testName()
    {
        $this->assertEquals('test', $this->tab()->name());
    }

    public function testSections()
    {
        $tab = $this->tab([
            'columns' => $this->columns()
        ]);

        $this->assertInstanceOf(Collection::class, $tab->sections());
        $this->assertCount(3, $tab->sections());
        $this->assertEquals('fields', $tab->sections()->first()->name());
        $this->assertEquals('gallery', $tab->sections()->last()->name());
    }

    public function testSection()
    {
        $tab = $this->tab([
            'columns' => $this->columns()
        ]);

        $this->assertInstanceOf(BlueprintSection::class, $tab->section('fields'));
        $this->assertEquals('fields', $tab->section('fields')->name());
    }

    public function testMissingSection()
    {
        $tab = $this->tab([
            'columns' => $this->columns()
        ]);

        $this->assertNull($tab->section('something'));
    }

}
