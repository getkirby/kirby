<?php

namespace Kirby\Cms;

class BlueprintConverterTest extends TestCase
{

    public function testConvertColumnsToTabs()
    {
        $blueprint = [
            'columns' => []
        ];

        $expected = [
            'tabs' => [
                'main' => [
                    'columns' => [],
                    'label'   => 'Main'
                ]
            ]
        ];

        $converted = BlueprintConverter::convertColumnsToTabs($blueprint);
        $this->assertEquals($expected, $converted);
    }

    public function testConvertSectionsToColumns()
    {
        $this->markTestIncomplete();
    }

    public function testConvertFieldsToSection()
    {
        $blueprint = [
            'fields' => []
        ];

        $expected = [
            'sections' => [
                'fields' => [
                    'type'   => 'fields',
                    'fields' => []
                ]
            ]
        ];

        $converted = BlueprintConverter::convertFieldsToSection($blueprint);
        $this->assertEquals($expected, $converted);
    }

}
