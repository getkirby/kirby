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

    public function testId()
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
            'locale' => 'en',
            'model'  => null
        ];

        $this->assertEquals($expected, $section->toArray());
    }

    public function testType()
    {
        $this->assertEquals('fields', $this->section()->type());
    }

}
