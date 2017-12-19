<?php

namespace Kirby\Cms;

class BlueprintConverterTest extends TestCase
{

    public function testConvertColumnsToTab()
    {
        $blueprint = [
            'columns' => []
        ];

        $expected = [
            'tabs' => [
                'main' => [
                    'columns' => []
                ]
            ]
        ];

        $converted = BlueprintConverter::convertColumnsToTab($blueprint);
        $this->assertEquals($expected, $converted);
    }

    public function testConvertSectionsToColumn()
    {
        $blueprint = [
            'sections' => []
        ];

        $expected = [
            'columns' => [
                'center' => [
                    'sections' => []
                ]
            ]
        ];

        $converted = BlueprintConverter::convertSectionsToColumn($blueprint);
        $this->assertEquals($expected, $converted);
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
