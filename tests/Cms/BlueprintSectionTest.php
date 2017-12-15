<?php

namespace Kirby\Cms;

class BlueprintSectionTest extends TestCase
{

    public function fields(): array
    {
        return [
            [
                'label' => 'Title',
                'name'  => 'title',
                'type'  => 'text'
            ],
            [
                'label' => 'Text',
                'name'  => 'text',
                'type'  => 'textarea'
            ]
        ];
    }

    public function section(array $props = [])
    {
        return new BlueprintSection(array_merge([
            'name' => 'test',
            'type' => 'fields',
        ], $props));
    }

    public function testFields()
    {
        $section = $this->section([
            'fields' => $this->fields()
        ]);

        $this->assertInstanceOf(Collection::class, $section->fields());
        $this->assertCount(2, $section->fields());
        $this->assertEquals('title', $section->fields()->first()->name());
        $this->assertEquals('text', $section->fields()->last()->name());
    }

    public function testField()
    {
        $section = $this->section([
            'fields' => $this->fields()
        ]);

        $expected       = $this->fields()[0];
        $expected['id'] = $expected['name'];

        $this->assertInstanceOf(BlueprintField::class, $section->field('title'));
        $this->assertEquals($expected, $section->field('title')->toArray());
    }

    public function testId()
    {
        $this->assertEquals('my-id', $this->section(['id' => 'my-id'])->id());
    }

    public function testDefaultId()
    {
        $this->assertEquals('test', $this->section()->id());
    }

    public function testName()
    {
        $this->assertEquals('test', $this->section()->name());
    }

    public function testToArray()
    {
        $section  = $this->section();
        $expected = [
            'name'   => 'test',
            'type'   => 'fields',
            'fields' => [],
            'id'     => 'test',
        ];

        $this->assertEquals($expected, $section->toArray());
    }

    public function testToArrayWithFields()
    {
        $section  = $this->section([
            'fields' => $this->fields()
        ]);

        $expected = [
            'name'   => 'test',
            'type'   => 'fields',
            'fields' => $section->fields()->toArray(),
            'id'     => 'test'
        ];

        $array = $section->toArray();

        $this->assertCount(2, $array['fields']);
        $this->assertArrayHasKey('title', $array['fields']);
        $this->assertArrayHasKey('text', $array['fields']);

        $this->assertEquals('title', $array['fields']['title']['id']);
        $this->assertEquals('text', $array['fields']['text']['id']);
    }

    public function testType()
    {
        $this->assertEquals('fields', $this->section()->type());
    }

}
